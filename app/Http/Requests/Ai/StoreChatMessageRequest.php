<?php

declare(strict_types=1);

namespace App\Http\Requests\Ai;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return \auth()->check();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'session_id' => ['required', 'string', 'max:64', 'exists:ai_chat_sessions,session_id'],
            'message' => ['required', 'string', 'max:5000'],
            'temperature' => ['sometimes', 'numeric', 'between:0,2'],
            'max_output_tokens' => ['sometimes', 'integer', 'min:1', 'max:8192'],
        ];
    }
}
