<?php

declare(strict_types=1);

namespace App\Http\Requests\User\PaymentMethod;

use App\Models\UserPaymentMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

class ReplacePaymentMethodRequest extends FormRequest
{
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
            'payment_type_id' => ['required', 'exists:payment_types,id'],
            'payment_status_id' => ['required', 'exists:payment_statuses,id'],
            'label' => ['required', 'string', 'max:255'],
            'card_holder_name' => ['required', 'string', 'max:255'],
            'card_number' => ['required', 'string', 'min:12', 'max:19'],
            'expiry_month' => ['required', 'integer', 'min:1', 'max:12'],
            'expiry_year' => ['required', 'integer', 'min:'.\date('Y'), 'max:'.(\date('Y') + 20)],
            'cvv' => ['required', 'string', 'min:3', 'max:4'],
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

                $paymentMethod = UserPaymentMethod::query()->find($paymentMethodId);
                if (! $paymentMethod) {
                    return;
                }

                $user = Auth::user();
                if ($user && $user->paymentMethods()->where('id', '!=', $paymentMethod->id)->where('is_default', true)->exists()) {
                    $validator->errors()->add('is_default', 'Only one payment method can be set as default.');
                }
            }
        });
    }
}
