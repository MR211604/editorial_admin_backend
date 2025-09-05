<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Traits\ApiResponse;

class Handler extends ExceptionHandler
{
    use ApiResponse;
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        // API friendly JSON responses
        if ($exception instanceof AuthenticationException) {
            return $this->unauthenticated($request, $exception);
        }

        if ($exception instanceof AuthorizationException) {
            return $this->forbiddenResponse('Acceso prohibido');
        }

        if ($exception instanceof ValidationException) {
            return $this->validationResponse($exception->errors(), 'Datos inválidos');
        }

        if ($exception instanceof ModelNotFoundException) {
            return $this->notFoundResponse('Recurso no encontrado');
        }

        if ($exception instanceof ApiException) {
            return $this->errorResponse($exception->getUserMessage(), $exception->getStatusCode(), $exception->getErrors());
        }

        if ($exception instanceof HttpException) {
            $status = $exception->getStatusCode();
            $message = 'Ha ocurrido un error';
            switch ($status) {
                case 400:
                    $message = 'Solicitud inválida';
                    break;
                case 401:
                    $message = 'No autorizado';
                    break;
                case 403:
                    $message = 'Acceso prohibido';
                    break;
                case 404:
                    $message = 'Recurso no encontrado';
                    break;
                case 409:
                    $message = 'Conflicto';
                    break;
                case 422:
                    $message = 'Datos inválidos';
                    break;
            }
            return $this->errorResponse($message, $status);
        }

        // Fallback genérico: no filtrar mensajes internos al usuario
        $debug = config('app.debug');
        if ($debug) {
            return $this->errorResponse($exception->getMessage(), 500);
        }

        return $this->errorResponse('Ha ocurrido un error', 500);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $this->unauthorizedResponse('Acceso denegado. Se requiere autenticación válida.');
    }
}
