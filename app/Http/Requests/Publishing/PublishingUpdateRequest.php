<?php

namespace App\Http\Requests\Publishing;

use Illuminate\Foundation\Http\FormRequest;

class PublishingUpdateRequest extends FormRequest
{
  public function authorize()
  {
    return true;
  }

  public function rules()
  {
    $rules = [
      'id' => 'required|integer|exists:publishings,id',
      'publishing_type' => 'required|integer|exists:publishing_types,id',
      'category_id' => 'nullable|integer|exists:categories,id',
      'is_new_arrival' => 'sometimes|boolean',
      'URL' => 'nullable|string',
      'authors' => 'sometimes|array',
      'authors.*' => 'integer|exists:authors,id',
      'cover' => 'sometimes',
    ];

    $type = (int) $this->input('publishing_type');

    if ($type === 1) {
      $rules = array_merge($rules, [
        'title' => 'required|string|max:255',
        'pages' => 'required|integer|min:1',
        'publish_date' => 'required|date',
        'publish_code' => [
          'required',
          'string',
          'regex:/^\d{3}-\d{2}-\d{4}-\d{3}-\d$/'
        ],
      ]);
    } elseif ($type === 2) {
      $rules = array_merge($rules, [
        'title' => 'required|string|max:255',
        'pages' => 'required|integer|min:1',
        'publish_date' => 'required|date',
        'publish_code' => [
          'required',
          'string',
          'regex:/^\d{3}-\d{2}-\d{4}-\d{3}-\d$/'
        ],
        'age' => 'nullable|integer|min:1',
        'age_number' => 'nullable|integer|min:1',
        'volume' => 'nullable|integer|min:1',
      ]);
    }

    return $rules;
  }

  protected function validationData()
  {
    return array_merge($this->all(), [
      'id' => $this->route('id'),
    ]);
  }
}
