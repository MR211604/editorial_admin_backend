<?php

namespace App\Http\Requests\Rol;

use Illuminate\Foundation\Http\FormRequest;

class RolIndexRequest extends FormRequest
{
  public function authorize()
  {
    return true;
  }

  public function rules()
  {
    return [
      'page' => 'nullable|integer|min:1',
      'limit' => 'nullable|integer|min:10',
    ];
  }
}
