<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\YouTubeService;
use App\Services\AudioConverterService;
use App\Services\DiskSpaceService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct(
        protected YouTubeService $youtubeService,
        protected AudioConverterService $audioConverterService,
        protected DiskSpaceService $diskSpaceService
    ) {}

    public function index()
    {
        // Get all settings from database
        $settings = Setting::all()->pluck('value', 'key');
        
        // Merge with defaults (in case some keys don't exist in DB yet)
        $defaults = [
            'download_path' => config('youtube-converter.download_path'),
            'max_playlist_size' => config('youtube-converter.max_playlist_size'),
            'max_concurrent_downloads' => config('youtube-converter.max_concurrent_downloads'),
            'min_disk_space_mb' => config('youtube-converter.min_disk_space_mb'),
        ];
        
        foreach ($defaults as $key => $value) {
            if (!isset($settings[$key])) {
                $settings[$key] = $value;
            }
        }
        
        return response()->json($settings);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'download_path' => 'required|string',
            'max_playlist_size' => 'required|integer|min:1',
            'max_concurrent_downloads' => 'required|integer|min:1|max:10',
            'min_disk_space_mb' => 'required|integer|min:100',
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        return response()->json(['message' => 'Settings updated successfully']);
    }

    public function checkDiskSpace()
    {
        $free = $this->diskSpaceService->checkAvailableSpace();
        $total = disk_total_space(config('youtube-converter.download_path')) / 1024 / 1024;
        
        return response()->json([
            'free_mb' => round($free, 2),
            'total_mb' => round($total, 2),
            'path' => config('youtube-converter.download_path')
        ]);
    }

    public function testDependencies()
    {
        $ytDlp = [
            'installed' => $this->youtubeService->checkYtDlpInstalled(),
            'version' => $this->youtubeService->getYtDlpVersion()
        ];
        
        $ffmpeg = [
            'installed' => $this->audioConverterService->validateFfmpegInstalled(),
            'version' => $this->audioConverterService->getFfmpegVersion() // Need to implement this if missing
        ];
        
        return response()->json([
            'yt_dlp' => $ytDlp,
            'ffmpeg' => $ffmpeg
        ]);
    }
}
