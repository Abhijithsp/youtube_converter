<?php

namespace App\Exceptions;

use Exception;

class InsufficientDiskSpaceException extends Exception
{
    protected $message = 'Insufficient disk space available for download';
    protected $code = 507; // HTTP 507 Insufficient Storage
}
