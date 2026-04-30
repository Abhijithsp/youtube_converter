<?php

namespace App\Exceptions;

use Exception;

class YouTubeDownloadException extends Exception
{
    protected $message = 'YouTube download failed';
}
