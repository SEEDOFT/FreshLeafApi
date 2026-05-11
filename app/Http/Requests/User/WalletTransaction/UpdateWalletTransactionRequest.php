<?php

declare(strict_types=1);

namespace App\Http\Requests\User\WalletTransaction;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

use function auth;

class UpdateWalletTransactionRequest extends FormRequest
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
            'wallet_transaction_type_id' => [
                'sometimes',
                'integer',
                'exists:wallet_transaction_types,id',
            ],
            'wallet_transaction_status_id' => [
                'sometimes',
                'integer',
                'exists:wallet_transaction_statuses,id',
            ],
            'amount' => [
                'sometimes',
                'numeric',
                'min:0.01',
            ],
            'reference_type' => [
                'nullable',
                'string',
                'max:255',
            ],
            'reference_id' => [
                'nullable',
                'integer',
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }
}
