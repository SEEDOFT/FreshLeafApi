<?php

namespace App\Http\Requests\User\Pin;

use Illuminate\Foundation\Http\FormRequest;

class SetPinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pin' => ['required', 'string', 'size:6', 'regex:/^\d{6}$/', 'confirmed'],
        ];
    }
}
