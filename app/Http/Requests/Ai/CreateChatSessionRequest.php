<?php

declare(strict_types=1);

namespace App\Http\Requests\Ai;

use Illuminate\Foundation\Http\FormRequest;

class CreateChatSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'session_id' => ['sometimes', 'string', 'max:64'],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
