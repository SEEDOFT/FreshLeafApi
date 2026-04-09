<?php

namespace App\Http\Requests\PaymentMethod;

use App\Models\UserPaymentMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdatePaymentMethodRequest extends FormRequest
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
            'payment_type_id' => ['sometimes', 'required', 'exists:payment_types,id'],
            'payment_status_id' => ['sometimes', 'required', 'exists:payment_statuses,id'],
            'label' => ['sometimes', 'required', 'string', 'max:255'],
            'card_holder_name' => ['sometimes', 'required', 'string', 'max:255'],
            'card_number' => ['sometimes', 'required', 'string', 'min:12', 'max:19'],
            'expiry_month' => ['sometimes', 'required', 'integer', 'min:1', 'max:12'],
            'expiry_year' => ['sometimes', 'required', 'integer', 'min:'.date('Y'), 'max:'.(date('Y') + 20)],
            'cvv' => ['sometimes', 'required', 'string', 'min:3', 'max:4'],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->boolean('is_default')) {
                $paymentMethod = $this->route('userPaymentMethod');
                if (! $paymentMethod) {
                    return;
                }
                $paymentMethodId = $paymentMethod instanceof UserPaymentMethod ? $paymentMethod->id : null;
                if ($paymentMethodId && auth()->user()->paymentMethods()->where('id', '!=', $paymentMethodId)->where('is_default', true)->exists()) {
                    $validator->errors()->add('is_default', 'Only one payment method can be set as default.');
                }
            }
        });
    }
}
