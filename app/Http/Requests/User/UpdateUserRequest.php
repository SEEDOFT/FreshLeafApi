<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return \auth()->check();
    }

    public function rules(): array
    {
        $user = $this->route('user') ?? Auth::user();
        $userId = $user?->id;

        return [
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'image' => ['sometimes', 'file', 'mimes:png,jpg', 'max:204800000'],
            'phone_number' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('users', 'phone_number')->ignore($userId),
            ],
            'password' => ['sometimes', 'string', 'min:8', 'confirmed'],
        ];
    }
}
