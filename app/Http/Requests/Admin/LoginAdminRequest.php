<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LoginAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['sometimes', 'required_without:phone_number', 'email', 'max:255'],
            'phone_number' => ['sometimes', 'required_without:email', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ];
    }
}
