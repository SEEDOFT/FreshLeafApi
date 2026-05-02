<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdminProfileRequest extends FormRequest
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
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'department' => ['sometimes', 'nullable', 'string', 'max:120'],
            'job_title' => ['sometimes', 'nullable', 'string', 'max:120'],
            'office_phone' => ['sometimes', 'nullable', 'string', 'max:40'],
            'locale' => ['sometimes', 'string', 'in:en,km'],
            'prefer_theme' => ['sometimes', 'string', 'in:system,light,dark'],
        ];
    }
}
