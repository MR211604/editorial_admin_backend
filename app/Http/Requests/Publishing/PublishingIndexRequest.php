<?php

namespace App\Http\Requests\Publishing;

use Illuminate\Foundation\Http\FormRequest;

class PublishingIndexRequest extends FormRequest
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
      'publishing_type' => 'nullable|integer',
      'category' => 'nullable|integer',
      'search' => 'nullable|string',
      'sort' => 'nullable|string',
      'is_archived' => 'nullable|in:0,1',
    ];
  }
}
