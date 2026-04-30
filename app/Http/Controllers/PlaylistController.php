<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use App\Models\PlaylistItem;
use App\Services\YouTubeService;
use App\Jobs\ProcessPlaylistJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class PlaylistController extends Controller
{
    public function __construct(
        protected YouTubeService $youtubeService
    ) {}

    /**
     * Fetch playlist info without saving
     */
    public function fetchInfo(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        try {
            $info = $this->youtubeService->extractPlaylistInfo($request->url);
            return response()->json($info);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch playlist info',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Create playlist and import items
     */
    public function store(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'title' => 'required|string',
            'items' => 'required|array', // Selected items
            'items.*.url' => 'required|url',
            'items.*.title' => 'required|string',
            'items.*.id' => 'required|string',
            'format' => 'required|in:mp3,flac',
            'quality' => 'required|in:192,256,320',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // Create Playlist record
                $playlistId = $this->youtubeService->extractPlaylistId($request->url);
                
                if ($request->items) {
                    $maxSize = \App\Models\Setting::get('max_playlist_size', config('youtube-converter.max_playlist_size', 50));
                    if (count($request->items) > $maxSize) {
                         throw new Exception("Playlist size exceeds limit of {$maxSize} videos.");
                    }
                }

                $playlist = Playlist::create([
                    'youtube_playlist_id' => $playlistId,
                    'title' => $request->title,
                    'total_videos' => count($request->items),
                    'status' => 'pending',
                ]);

                // Create Item records
                foreach ($request->items as $index => $item) {
                    PlaylistItem::create([
                        'playlist_id' => $playlist->id,
                        'youtube_url' => $item['url'],
                        'position' => $index + 1,
                        'selected' => true,
                    ]);
                }

                // Dispatch Job
                ProcessPlaylistJob::dispatch(
                    $playlist, 
                    $request->format, 
                    (int) $request->quality
                );

                return response()->json([
                    'message' => 'Playlist processing started',
                    'playlist' => $playlist->load('items'),
                ], 201);
            });

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to queue playlist',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function index()
    {
        $playlists = Playlist::withCount('items')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return response()->json($playlists);
    }

    public function show(Playlist $playlist)
    {
        return response()->json(
            $playlist->load(['items.download'])
        );
    }

    public function destroy(Playlist $playlist)
    {
        $playlist->delete();
        return response()->json(['message' => 'Playlist deleted']);
    }
}
