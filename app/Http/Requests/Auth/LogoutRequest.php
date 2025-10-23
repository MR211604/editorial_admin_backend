<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LogoutRequest extends FormRequest
{
  /**
   * Determina si el usuario está autorizado a realizar la solicitud.
   */
  public function authorize()
  {
    return true;
  }

  /**
   * Expone el token del header como dato validable.
   */
  protected function prepareForValidation()
  {
    $this->merge([
      'token' => $this->bearerToken(),
    ]);
  }

  /**
   * Reglas de validación para cerrar sesión.
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
   * Mantiene el formato de respuesta en caso de validación fallida.
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
