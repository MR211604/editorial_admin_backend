<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RenewTokenRequest extends FormRequest
{
  /**
   * Determina si el usuario está autorizado a realizar la solicitud.
   */
  public function authorize()
  {
    return true;
  }

  /**
   * Asegura que el token se expone como dato validable.
   */
  protected function prepareForValidation()
  {
    $this->merge([
      'token' => $this->bearerToken(),
    ]);
  }

  /**
   * Reglas de validación para renovar el token.
   */
  public function rules()
  {
    return [
      'token' => ['required', 'string'],
    ];
  }

  /**
   * Mensajes personalizados para errores de validación.
   */
  public function messages()
  {
    return [
      'token.required' => 'Se requiere un token de autorización',
      'token.string' => 'El token proporcionado no es válido',
    ];
  }

  /**
   * Responde con el mismo formato de error que utilizaba el controlador.
   */
  protected function failedValidation(Validator $validator)
  {
    throw new HttpResponseException(
      response()->json([
        'ok' => false,
        'error' => 'Token no proporcionado',
        'message' => 'Se requiere un token de autorización',
      ], 401)
    );
  }
}
