<?php

namespace App\Http\Requests\Publishing;

use Illuminate\Foundation\Http\FormRequest;

class PublishingStoreRequest extends FormRequest
{
  public function authorize()
  {
    return true;
  }

  public function rules()
  {
    return [
      'title' => 'required|string|max:255',
      'URL' => 'nullable|string',
      'category_id' => 'nullable|integer|exists:categories,id',
      'publishing_type' => 'required|integer|exists:publishing_types,id',
      'is_new_arrival' => 'nullable|boolean',
      'is_archived' => 'nullable|boolean',
    ];
  }
}
