<?php

namespace App\Exceptions;

class ServerErrorException extends ApiException
{
    protected $statusCode = 500;
}
