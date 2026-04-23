<?php

declare(strict_types=1);

namespace App\Http\Requests\Ai;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class FetchChatHistoryRequest extends FormRequest
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
            'limit' => ['sometimes', 'integer', 'min:1', 'max:500'],
        ];
    }
}
