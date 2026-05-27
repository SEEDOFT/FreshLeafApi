<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Cart;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

use function auth;

class CartCheckoutRequest extends FormRequest
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
            'address_id' => ['required', 'integer', 'exists:addresses,id'],
            'payment_method_type_id' => ['required', 'integer', 'exists:payment_method_types,id'],
            'order_type_id' => ['required', 'integer'],
            'delivery_date' => ['nullable', 'date'],
            'delivery_slot' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
