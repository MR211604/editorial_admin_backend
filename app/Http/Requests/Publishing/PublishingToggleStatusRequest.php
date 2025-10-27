<?php

namespace App\Http\Requests\Publishing;

use Illuminate\Foundation\Http\FormRequest;

class PublishingToggleStatusRequest extends FormRequest
{
  public function authorize()
  {
    return true;
  }

  public function rules()
  {
    return [
      'is_archived' => 'required',
    ];
  }
}
