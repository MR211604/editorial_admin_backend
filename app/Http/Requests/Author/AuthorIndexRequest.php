<?php

namespace App\Http\Requests\Author;

use Illuminate\Foundation\Http\FormRequest;

class AuthorIndexRequest extends FormRequest
{
  public function authorize()
  {
    return true;
  }

  public function rules()
  {
    return [
      'page' => 'integer|min:1',
      'limit' => 'integer|min:10',
      'search' => 'nullable|string',
      'sort' => 'nullable|string',
    ];
  }
}
