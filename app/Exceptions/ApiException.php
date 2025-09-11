<?php

namespace App\Exceptions;

use Exception;

abstract class ApiException extends Exception
{
    /** @var int */
    protected $statusCode = 500;
    /** @var array|null */
    protected $errors = null;
    /** @var string */
    protected $userMessage;

    public function __construct($userMessage = 'Ha ocurrido un error', $errors = null, Exception $previous = null)
    {
        parent::__construct($userMessage, 0, $previous);
        $this->userMessage = $userMessage;
        $this->errors = $errors;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getUserMessage()
    {
        return $this->userMessage;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
