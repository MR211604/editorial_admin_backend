<?php

namespace App\Exceptions;

class ForbiddenException extends ApiException
{
    protected $statusCode = 403;
}
