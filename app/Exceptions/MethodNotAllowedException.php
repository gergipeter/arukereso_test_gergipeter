<?php

namespace App\Exceptions;

use Exception;

class MethodNotAllowedException extends Exception
{
    public function __construct($message = 'The specified method is not allowed.', $code = 405, $allowedMethods = [])
    {
        parent::__construct($message, $code);
        $this->allowedMethods = $allowedMethods;
    }
}
