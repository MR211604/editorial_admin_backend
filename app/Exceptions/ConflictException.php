<?php

namespace App\Exceptions;

class ConflictException extends ApiException
{
    protected $statusCode = 409;
}
