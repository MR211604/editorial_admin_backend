<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
  /**
   * Determina si el usuario está autorizado a realizar la solicitud.
   */
  public function authorize()
  {
    return true;
  }

  /**
   * Reglas de validación para iniciar sesión.
   */
  public function rules()
  {
    return [
      'token' => ['required', 'string'],
      'password' => ['required', 'string'],
    ];
  }

  /**
   * Mensajes personalizados para errores de validación.
   */
  public function messages()
  {
    return [
      'token.required' => 'El identificador de login es obligatorio.',
      'token.string' => 'El identificador de login debe ser una cadena de texto.',
      'password.required' => 'La contraseña es obligatoria.',
      'password.string' => 'La contraseña debe ser una cadena de texto.',
    ];
  }
}
