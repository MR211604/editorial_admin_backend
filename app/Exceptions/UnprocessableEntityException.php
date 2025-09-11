<?php

namespace App\Exceptions;

class UnprocessableEntityException extends ApiException
{
    protected $statusCode = 422;
}
