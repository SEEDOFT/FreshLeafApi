<?php

namespace App\Http\Requests\Ai;

use Illuminate\Foundation\Http\FormRequest;

class FetchChatHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'session_id' => ['required', 'string', 'max:64', 'exists:ai_chat_sessions,session_id'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:500'],
        ];
    }
}
