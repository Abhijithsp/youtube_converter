<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Download Path
    |--------------------------------------------------------------------------
    |
    | The directory where downloaded audio files will be stored.
    |
    */
    'download_path' => env('YOUTUBE_DOWNLOAD_PATH', storage_path('app/downloads')),

    /*
    |--------------------------------------------------------------------------
    | Max Playlist Size
    |--------------------------------------------------------------------------
    |
    | Maximum number of videos allowed in a playlist import.
    |
    */
    'max_playlist_size' => env('MAX_PLAYLIST_SIZE', 50),

    /*
    |--------------------------------------------------------------------------
    | Max Concurrent Downloads
    |--------------------------------------------------------------------------
    |
    | Maximum number of downloads that can be processed simultaneously.
    |
    */
    'max_concurrent_downloads' => env('MAX_CONCURRENT_DOWNLOADS', 3),

    /*
    |--------------------------------------------------------------------------
    | Rate Limit Seconds
    |--------------------------------------------------------------------------
    |
    | Delay in seconds between consecutive download jobs.
    |
    */
    'rate_limit_seconds' => env('RATE_LIMIT_SECONDS', 2),

    /*
    |--------------------------------------------------------------------------
    | Minimum Disk Space (MB)
    |--------------------------------------------------------------------------
    |
    | Minimum required disk space in megabytes before allowing downloads.
    |
    */
    'min_disk_space_mb' => env('MIN_DISK_SPACE_MB', 500),

    /*
    |--------------------------------------------------------------------------
    | Supported Formats
    |--------------------------------------------------------------------------
    |
    | Audio formats supported by the converter.
    |
    */
    'formats' => ['mp3', 'flac'],

    /*
    |--------------------------------------------------------------------------
    | Quality Options
    |--------------------------------------------------------------------------
    |
    | Available bitrate options for audio conversion (in kbps).
    |
    */
    'quality_options' => [192, 256, 320],

    /*
    |--------------------------------------------------------------------------
    | Job Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum time in seconds for a download job to complete.
    |
    */
    'job_timeout' => env('YOUTUBE_JOB_TIMEOUT', 600),

    /*
    |--------------------------------------------------------------------------
    | Job Retry Attempts
    |--------------------------------------------------------------------------
    |
    | Number of times to retry a failed download job.
    |
    */
    'job_tries' => env('YOUTUBE_JOB_TRIES', 3),

    /*
    |--------------------------------------------------------------------------
    | Job Backoff (seconds)
    |--------------------------------------------------------------------------
    |
    | Exponential backoff delays for job retries.
    |
    */
    'job_backoff' => [60, 300, 900],
];
