<?php

declare(strict_types=1);

namespace App\Http\Requests\Preference;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePanelPreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'locale' => ['sometimes', 'required', 'string', 'in:km,en'],
            'theme' => ['sometimes', 'required', 'string', 'in:light,dark'],
        ];
    }
}
