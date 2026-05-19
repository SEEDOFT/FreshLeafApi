<?php

declare(strict_types=1);

namespace App\Http\Requests\User\Auth;

use App\Models\UserType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
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
            'email' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'email')
                    ->where(static function (Builder $query): void {
                        $query->where('user_type_id', UserType::CONSUMER_ID)
                            ->whereNull('deleted_at');
                    }),
            ],
            'phone_number' => [
                'required',
                'string',
                'max:20',
                'starts_with:+855',
                Rule::unique('users', 'phone_number')
                    ->where(static function (Builder $query): void {
                        $query->where('user_type_id', UserType::CONSUMER_ID)
                            ->whereNull('deleted_at');
                    }),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
