<?php

namespace App\Exceptions;

use Exception;

class AudioConversionException extends Exception
{
    protected $message = 'Audio conversion failed';
}
