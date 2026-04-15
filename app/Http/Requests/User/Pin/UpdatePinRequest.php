<?php

declare(strict_types=1);

namespace App\Http\Requests\User\Pin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_pin' => ['required', 'string', 'size:6', 'regex:/^\d{6}$/'],
            'pin' => ['required', 'string', 'size:6', 'regex:/^\d{6}$/', 'different:current_pin', 'confirmed'],
        ];
    }
}
