<?php

namespace App\Exceptions;

class BadRequestException extends ApiException
{
    protected $statusCode = 400;
}
