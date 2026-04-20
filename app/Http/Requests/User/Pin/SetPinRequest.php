<?php

declare(strict_types=1);

namespace App\Http\Requests\User\Pin;

use Illuminate\Foundation\Http\FormRequest;

class SetPinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return \auth()->check();
    }

    public function rules(): array
    {
        return [
            'pin' => ['required', 'string', 'size:6', 'regex:/^\d{6}$/', 'confirmed'],
        ];
    }
}
