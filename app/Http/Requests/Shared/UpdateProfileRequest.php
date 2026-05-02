<?php

declare(strict_types=1);

namespace App\Http\Requests\Shared;

use App\Models\User;
use App\Models\UserType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return \auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->user();
        $userId = $user->id;

        $commonRules = [
            'locale' => ['sometimes', 'string', 'in:en,km'],
            'prefer_theme' => ['sometimes', 'string', 'in:system,light,dark'],
            'image' => ['sometimes', 'file', 'mimes:png,jpg', 'max:204800000'],
        ];

        $typeSpecificRules = match ((int) $user->user_type_id) {
            UserType::ADMIN => [
                'department' => ['sometimes', 'nullable', 'string', 'max:120'],
                'job_title' => ['sometimes', 'nullable', 'string', 'max:120'],
                'office_phone' => ['sometimes', 'nullable', 'string', 'max:40'],
            ],
            UserType::VENDOR => [
                'business_name' => ['sometimes', 'string', 'max:255'],
                'contact_phone' => ['sometimes', 'nullable', 'string', 'max:50'],
                'city' => ['sometimes', 'nullable', 'string', 'max:100'],
                'province' => ['sometimes', 'nullable', 'string', 'max:100'],
                'address' => ['sometimes', 'nullable', 'string', 'max:1000'],
                'meta' => ['sometimes', 'nullable', 'array'],
            ],
            UserType::USER => [
                'first_name' => ['sometimes', 'string', 'max:255'],
                'last_name' => ['sometimes', 'string', 'max:255'],
                'email' => [
                    'sometimes',
                    'nullable',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email')->ignore($userId),
                ],
                'phone_number' => [
                    'sometimes',
                    'string',
                    'max:20',
                    Rule::unique('users', 'phone_number')->ignore($userId),
                ],
                'password' => ['sometimes', 'string', 'min:8', 'confirmed'],
            ],
            default => [],
        };

        return \array_merge($commonRules, $typeSpecificRules);
    }
}
