<?php

namespace App\Http\Traits;

trait ApiResponse
{
    /**
     * Respuesta exitosa
     */
    public function successResponse($data = null, $message = 'Operación exitosa', $pagination = null, $code = 200)
    {
        return response()->json([
            'ok' => true,
            'message' => $message,
            'data' => $data,
            'pagination' => $pagination
        ], $code);
    }

    /**
     * Respuesta de error
     */
    public function errorResponse($message = 'Ha ocurrido un error', $code = 400, $errors = null)
    {
        $response = [
            'ok' => false,
            'error' => $message,
            'message' => $message
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Respuesta de no autorizado
     */
    public function unauthorizedResponse($message = 'No autorizado')
    {
        return response()->json([
            'ok' => false,
            'error' => 'No autorizado',
            'message' => $message,
            'hint' => 'Incluye el header: Authorization: Bearer {token}'
        ], 401);
    }

    /**
     * Respuesta de acceso prohibido
     */
    public function forbiddenResponse($message = 'Acceso prohibido')
    {
        return response()->json([
            'ok' => false,
            'error' => 'Acceso prohibido',
            'message' => $message
        ], 403);
    }

    /**
     * Respuesta de no encontrado
     */
    public function notFoundResponse($message = 'Recurso no encontrado')
    {
        return response()->json([
            'ok' => false,
            'error' => 'No encontrado',
            'message' => $message
        ], 404);
    }

    /**
     * Respuesta de validación
     */
    public function validationResponse($errors, $message = 'Datos inválidos')
    {
        return response()->json([
            'ok' => false,
            'error' => 'Validación fallida',
            'message' => $message,
            'errors' => $errors
        ], 422);
    }
}
