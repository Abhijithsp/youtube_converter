<?php

namespace App\Services;

use App\Models\Download;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class UrlGeneratorService
{
    /**
     * Generate a signed download URL for a download
     */
    public function generateDownloadUrl(Download $download): string
    {
        if (!$download->file_path || !file_exists($download->file_path)) {
            return '';
        }

        // Generate a signed URL that expires in 24 hours
        return URL::temporarySignedRoute(
            'downloads.stream',
            now()->addHours(24),
            ['download' => $download->id]
        );
    }

    /**
     * Generate a permanent download token
     */
    public function generateDownloadToken(Download $download): string
    {
        $token = Str::random(64);
        
        $download->update([
            'download_url' => route('downloads.stream-token', ['token' => $token])
        ]);

        // Store token in cache with download ID
        cache()->put("download_token:{$token}", $download->id, now()->addDays(7));

        return $token;
    }

    /**
     * Validate download token and get download
     */
    public function validateDownloadToken(string $token): ?Download
    {
        $downloadId = cache()->get("download_token:{$token}");

        if (!$downloadId) {
            return null;
        }

        return Download::find($downloadId);
    }

    /**
     * Revoke download token
     */
    public function revokeDownloadToken(string $token): bool
    {
        return cache()->forget("download_token:{$token}");
    }

    /**
     * Generate a shareable link for a download
     */
    public function generateShareableLink(Download $download): string
    {
        $token = $this->generateDownloadToken($download);
        
        return url("/share/{$token}");
    }
}
