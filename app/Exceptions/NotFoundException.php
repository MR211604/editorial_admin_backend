<?php

namespace App\Exceptions;

class NotFoundException extends ApiException
{
    protected $statusCode = 404;
}
