<?php

namespace App\Exceptions;

class UnauthorizedException extends ApiException
{
    protected $statusCode = 401;
}
