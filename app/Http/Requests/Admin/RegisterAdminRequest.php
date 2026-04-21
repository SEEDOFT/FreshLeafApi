<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\UserType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterAdminRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone_number' => [
                'required',
                'string',
                'max:20',
                'starts_with:+855',
                Rule::unique('users', 'phone_number')
                    ->where(static function ($query): void {
                        $query->where('user_type_id', UserType::ADMIN)
                            ->whereNull('deleted_at');
                    }),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'department' => ['sometimes', 'nullable', 'string', 'max:120'],
            'job_title' => ['sometimes', 'nullable', 'string', 'max:120'],
            'office_phone' => ['sometimes', 'nullable', 'string', 'max:40'],
            'super_admin' => ['sometimes', 'boolean'],
            'permissions' => ['sometimes', 'array'],
        ];
    }
}
