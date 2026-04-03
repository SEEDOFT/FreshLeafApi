<?php

namespace App\Http\Requests\PaymentMethod;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePaymentMethodRequest extends FormRequest
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
            'payment_type_id' => ['required', 'exists:payment_types,id'],
            'label' => ['required', 'string', 'max:255'],
            'card_holder_name' => ['required', 'string', 'max:255'],
            'card_number' => ['required', 'string', 'min:12', 'max:19'],
            'expiry_month' => ['required', 'integer', 'min:1', 'max:12'],
            'expiry_year' => ['required', 'integer', 'min:'.date('Y'), 'max:'.(date('Y') + 20)],
            'cvv' => ['required', 'string', 'min:3', 'max:4'],
            'is_default' => ['sometimes', 'boolean'],
            'billing_address' => ['sometimes', 'string', 'max:255'],
            'billing_city' => ['sometimes', 'string', 'max:255'],
            'billing_state' => ['sometimes', 'string', 'max:255'],
            'billing_zip_code' => ['sometimes', 'string', 'max:20'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->boolean('is_default') && auth()->user()->paymentMethods()->where('is_default', true)->exists()) {
                $validator->errors()->add('is_default', 'Only one payment method can be set as default.');
            }
        });
    }
}
