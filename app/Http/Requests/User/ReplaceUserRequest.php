<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReplaceUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return \auth()->check();
    }

    public function rules(): array
    {
        /** @var User|null $user */
        $user = \auth()->user();

        $userId = $user->id ?? 0;

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'image' => ['required', 'file', 'mimes:png,jpg', 'max:204800000'],
            'phone_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users', 'phone_number')->ignore($userId),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
