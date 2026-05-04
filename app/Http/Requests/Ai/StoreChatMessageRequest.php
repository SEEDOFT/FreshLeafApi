<?php

declare(strict_types=1);

namespace App\Http\Requests\Ai;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreChatMessageRequest extends FormRequest
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
        return [
            'session_id' => ['required', 'string', 'max:64', 'exists:ai_chat_sessions,session_id'],
            'message' => ['required', 'string', 'max:5000'],
            'temperature' => ['sometimes', 'numeric', 'between:0,2'],
            'max_output_tokens' => ['sometimes', 'integer', 'min:1', 'max:8192'],
        ];
    }
}
