<?php

declare(strict_types=1);

namespace App\Http\Requests\Ai;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

use function auth;

class CreateChatSessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'session_id' => ['sometimes', 'string', 'max:64'],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
