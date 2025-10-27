<?php

namespace App\Http\Requests\Publishing;

use Illuminate\Foundation\Http\FormRequest;

class PublishingShowRequest extends FormRequest
{
  public function authorize()
  {
    return true;
  }

  public function rules()
  {
    return [
      'id' => 'required|integer|exists:publishings,id',
    ];
  }

  protected function validationData()
  {
    return array_merge($this->all(), [
      'id' => $this->route('id'),
    ]);
  }
}
