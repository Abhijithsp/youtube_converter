<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Download;
use App\Services\YouTubeService;
use App\Services\AudioConverterService;
use App\Services\MetadataService;
use App\Services\DiskSpaceService;
use App\Services\UrlGeneratorService;
use App\Exceptions\InsufficientDiskSpaceException;
use App\Exceptions\YouTubeDownloadException;
use App\Exceptions\AudioConversionException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class DownloadYoutubeAudioJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels, Batchable;

    public $timeout = 600; // 10 minutes
    public $tries = 3;
    public $backoff = [60, 300, 900]; // Exponential backoff: 1min, 5min, 15min

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Download $download
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        YouTubeService $youtubeService,
        AudioConverterService $audioConverter,
        MetadataService $metadataService,
        DiskSpaceService $diskSpaceService,
        UrlGeneratorService $urlGenerator
    ): void
    {
        try {
            // Mark as processing
            $this->download->markAsProcessing();
            Log::info('Starting download job', ['download_id' => $this->download->id]);

            // Step 1: Check disk space
            $minSpace = (int) \App\Models\Setting::get('min_disk_space_mb', config('youtube-converter.min_disk_space_mb', 500));
            if (!$diskSpaceService->hasEnoughSpace($minSpace)) {
                throw new InsufficientDiskSpaceException(
                    "Not enough disk space available. Required: {$minSpace}MB."
                );
            }

            // Step 2: Extract video info if not already done
            if (!$this->download->youtube_id) {
                $videoInfo = $youtubeService->extractVideoInfo($this->download->youtube_url);
                
                $this->download->update([
                    'youtube_id' => $videoInfo['id'],
                    'title' => $videoInfo['title'],
                    'artist' => $videoInfo['artist'] ?? $this->download->artist,
                    'thumbnail_url' => $videoInfo['thumbnail'],
                    'duration' => $videoInfo['duration'],
                ]);
            }

            $this->download->updateProgress(10);

            // Step 3: Prepare download paths
            $downloadPath = config('youtube-converter.download_path');
            if (!is_dir($downloadPath)) {
                mkdir($downloadPath, 0755, true);
            }

            $tempPath = storage_path('app/temp');
            if (!is_dir($tempPath)) {
                mkdir($tempPath, 0755, true);
            }

            $safeFilename = Str::slug($this->download->title);
            $tempFile = $tempPath . '/' . $safeFilename . '_' . time();
            $finalFile = $downloadPath . '/' . $safeFilename . '.' . $this->download->format;

            // Make filename unique if it already exists
            $counter = 1;
            while (file_exists($finalFile)) {
                $finalFile = $downloadPath . '/' . $safeFilename . '_' . $counter . '.' . $this->download->format;
                $counter++;
            }

            $this->download->updateProgress(20);

            // Step 4: Download audio using yt-dlp
            Log::info('Downloading audio', ['url' => $this->download->youtube_url]);
            
            try {
                $youtubeService->downloadAudio(
                    $this->download->youtube_url,
                    $tempFile,
                    $this->download->format,
                    (int) $this->download->quality
                );
            } catch (Exception $e) {
                throw new YouTubeDownloadException(
                    'Failed to download audio from YouTube: ' . $e->getMessage()
                );
            }

            $this->download->updateProgress(60);

            // Step 5: Find the downloaded file (yt-dlp adds extension)
            $downloadedFile = null;
            foreach (glob($tempFile . '*') as $file) {
                if (is_file($file)) {
                    $downloadedFile = $file;
                    break;
                }
            }

            if (!$downloadedFile || !file_exists($downloadedFile)) {
                throw new YouTubeDownloadException('Downloaded file not found');
            }

            $this->download->updateProgress(70);

            // Step 6: Convert if necessary (yt-dlp should handle this, but double-check)
            $needsConversion = pathinfo($downloadedFile, PATHINFO_EXTENSION) !== $this->download->format;
            
            if ($needsConversion) {
                Log::info('Converting audio format');
                
                try {
                    $audioConverter->convert(
                        $downloadedFile,
                        $finalFile,
                        $this->download->format,
                        (int) $this->download->quality
                    );
                    
                    // Remove temp file after conversion
                    @unlink($downloadedFile);
                } catch (Exception $e) {
                    throw new AudioConversionException(
                        'Failed to convert audio: ' . $e->getMessage()
                    );
                }
            } else {
                // Just move the file
                rename($downloadedFile, $finalFile);
            }

            $this->download->updateProgress(80);

            // Step 7: Apply metadata tags
            Log::info('Applying metadata tags');
            
            try {
                $metadata = [
                    'title' => $this->download->title,
                    'artist' => $this->download->artist,
                    'album' => $this->download->album,
                ];

                $metadataService->tagFile($finalFile, $metadata);

                // Download and attach thumbnail if available
                if ($this->download->thumbnail_url) {
                    $thumbnailPath = $tempPath . '/' . $safeFilename . '_thumb.jpg';
                    
                    if ($metadataService->downloadThumbnail($this->download->thumbnail_url, $thumbnailPath)) {
                        $metadataService->attachThumbnail($finalFile, $thumbnailPath);
                        @unlink($thumbnailPath);
                    }
                }
            } catch (Exception $e) {
                // Metadata tagging is not critical, log and continue
                Log::warning('Metadata tagging failed', ['error' => $e->getMessage()]);
            }

            $this->download->updateProgress(90);

            // Step 8: Get file info and generate download URL
            $fileSize = filesize($finalFile);
            $downloadUrl = $urlGenerator->generateDownloadUrl($this->download);

            // Step 9: Mark as completed
            $this->download->update([
                'status' => 'completed',
                'file_path' => $finalFile,
                'file_size' => $fileSize,
                'download_url' => $downloadUrl,
                'progress' => 100,
                'completed_at' => now(),
            ]);

            Log::info('Download completed successfully', [
                'download_id' => $this->download->id,
                'file_path' => $finalFile,
            ]);

        } catch (Exception $e) {
            Log::error('Download job failed', [
                'download_id' => $this->download->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->download->markAsFailed($e->getMessage());

            // Clean up any temp files
            if (isset($tempFile)) {
                foreach (glob($tempFile . '*') as $file) {
                    @unlink($file);
                }
            }

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('Download job failed permanently', [
            'download_id' => $this->download->id,
            'error' => $exception->getMessage(),
        ]);

        $this->download->markAsFailed(
            'Download failed after ' . $this->tries . ' attempts: ' . $exception->getMessage()
        );
    }
}
