<?php

declare(strict_types=1);

namespace App\Http\Requests\User\Profile;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use function auth;

class UpdateProfileRequest extends FormRequest
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
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->where(function (Builder $query): void {
                        $query->where('user_type_id', $this->user()?->user_type_id);
                    })
                    ->ignore($this->user()),
            ],
            'phone_number' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('users', 'phone_number')
                    ->where(function (Builder $query): void {
                        $query->where('user_type_id', $this->user()?->user_type_id);
                    })
                    ->ignore($this->user()),
            ],
            'image' => ['sometimes', 'image', 'mimes:jpeg,png,jpg', 'max:6144'],
            'locale' => ['sometimes', 'string', 'in:km,en'],
            'theme' => ['sometimes', 'string', 'in:system,light,dark'],
        ];
    }
}
