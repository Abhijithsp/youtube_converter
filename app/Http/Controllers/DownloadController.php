<?php

namespace App\Http\Controllers;

use App\Models\Download;
use App\Jobs\DownloadYoutubeAudioJob;
use App\Services\YouTubeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    public function __construct(
        private YouTubeService $youtubeService
    ) {}

    /**
     * Display a listing of downloads
     */
    public function index(Request $request): JsonResponse
    {
        $query = Download::query()->latest();

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by title or artist
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('artist', 'like', "%{$search}%");
            });
        }

        $downloads = $query->paginate($request->get('per_page', 20));

        return response()->json($downloads);
    }

    /**
     * Store a newly created download
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'youtube_url' => 'required|url',
            'format' => 'required|in:mp3,flac',
            'quality' => 'required|in:192,256,320',
            'artist' => 'nullable|string|max:255',
            'album' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate YouTube URL
        if (!$this->youtubeService->validateUrl($request->youtube_url)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid YouTube URL'
            ], 422);
        }

        try {
            // Extract video ID
            $videoId = $this->youtubeService->extractVideoId($request->youtube_url);

            // Check if already downloaded
            $existing = Download::where('youtube_id', $videoId)
                ->where('status', 'completed')
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => true,
                    'message' => 'This video has already been downloaded',
                    'download' => $existing
                ]);
            }

            // Create download record
            $download = Download::create([
                'youtube_url' => $request->youtube_url,
                'youtube_id' => $videoId,
                'title' => $request->title ?? 'Fetching...',
                'thumbnail_url' => $request->thumbnail_url,
                'format' => $request->format,
                'quality' => (string) $request->quality,
                'artist' => $request->artist,
                'album' => $request->album,
                'status' => 'pending',
            ]);

            // Dispatch job
            DownloadYoutubeAudioJob::dispatch($download);

            return response()->json([
                'success' => true,
                'message' => 'Download started successfully',
                'download' => $download
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start download: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch video information without creating a download
     */
    public function fetchInfo(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'youtube_url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate YouTube URL
        if (!$this->youtubeService->validateUrl($request->youtube_url)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid YouTube URL'
            ], 422);
        }

        try {
            // Extract video info
            $videoInfo = $this->youtubeService->extractVideoInfo($request->youtube_url);

            return response()->json([
                'success' => true,
                'video' => $videoInfo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch video info: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified download
     */
    public function show(Download $download): JsonResponse
    {
        return response()->json([
            'success' => true,
            'download' => $download
        ]);
    }

    /**
     * Remove the specified download
     */
    public function destroy(Download $download): JsonResponse
    {
        try {
            // Delete file if exists
            if ($download->file_path && file_exists($download->file_path)) {
                unlink($download->file_path);
            }

            $download->delete();

            return response()->json([
                'success' => true,
                'message' => 'Download deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete download: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retry a failed download
     */
    public function retry(Download $download): JsonResponse
    {
        if ($download->status !== 'failed') {
            return response()->json([
                'success' => false,
                'message' => 'Only failed downloads can be retried'
            ], 422);
        }

        try {
            $download->update([
                'status' => 'pending',
                'error_message' => null,
                'progress' => 0,
            ]);

            DownloadYoutubeAudioJob::dispatch($download);

            return response()->json([
                'success' => true,
                'message' => 'Download retry started',
                'download' => $download
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retry download: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a pending or processing download
     */
    public function cancel(Download $download): JsonResponse
    {
        if (!in_array($download->status, ['pending', 'processing'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending or processing downloads can be cancelled'
            ], 422);
        }

        try {
            $download->update([
                'status' => 'cancelled',
                'error_message' => 'Cancelled by user'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Download cancelled successfully',
                'download' => $download
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel download: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Stream/download the audio file
     */
    public function stream(Download $download): StreamedResponse
    {
        if ($download->status !== 'completed' || !$download->file_path) {
            abort(404, 'File not found');
        }

        if (!file_exists($download->file_path)) {
            abort(404, 'File not found on disk');
        }

        $filename = basename($download->file_path);

        return response()->stream(function() use ($download) {
            $stream = fopen($download->file_path, 'r');
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => 'audio/' . $download->format,
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => filesize($download->file_path),
        ]);
    }
}
