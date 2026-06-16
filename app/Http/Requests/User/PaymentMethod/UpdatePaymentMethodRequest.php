<?php

declare(strict_types=1);

namespace App\Http\Requests\User\PaymentMethod;

use App\Models\PaymentMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

class UpdatePaymentMethodRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $normalized = [];

        if ($this->has('payment_method_type_id') || $this->has('payment_type_id')) {
            $normalized['payment_method_type_id'] = $this->input(
                'payment_method_type_id',
                $this->input('payment_type_id')
            );
        }

        if ($this->has('payment_method_status_id') || $this->has('payment_status_id')) {
            $normalized['payment_method_status_id'] = $this->input(
                'payment_method_status_id',
                $this->input('payment_status_id')
            );
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }

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
            'payment_method_type_id' => ['sometimes', 'required', 'exists:payment_method_types,id'],
            'payment_method_status_id' => ['sometimes', 'required', 'exists:payment_method_statuses,id'],
            'label' => ['sometimes', 'required', 'string', 'max:255'],
            'card_holder_name' => ['sometimes', 'required', 'string', 'max:255'],
            'card_number' => ['sometimes', 'required', 'string', 'min:12', 'max:19'],
            'expiry_month' => ['sometimes', 'required', 'integer', 'min:1', 'max:12'],
            'expiry_year' => ['sometimes', 'required', 'integer', 'min:'.\date('Y'), 'max:'.(\date('Y') + 20)],
            'cvv' => ['sometimes', 'required', 'string', 'min:3', 'max:4'],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->boolean('is_default')) {
                $paymentMethodId = (int) $this->route('id');
                if ($paymentMethodId <= 0) {
                    return;
                }

                $paymentMethod = PaymentMethod::query()->find($paymentMethodId);
                if (! $paymentMethod) {
                    return;
                }

                $user = Auth::user();
                if ($user && $user->paymentMethods()->where('id', '!=', $paymentMethodId)->where('is_default', true)->exists()) {
                    $validator->errors()->add('is_default', 'Only one payment method can be set as default.');
                }
            }
        });
    }
}
