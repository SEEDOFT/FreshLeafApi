<?php

declare(strict_types=1);

namespace App\Http\Requests\User\WalletTransaction;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

use function auth;

class StoreWalletTransactionRequest extends FormRequest
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
            'wallet_id' => [
                'required',
                'integer',
                'exists:wallets,id',
            ],
            'currency_id' => [
                'required',
                'integer',
                'exists:currencies,id',
            ],
            'wallet_transaction_type_id' => [
                'required',
                'integer',
                'exists:wallet_transaction_types,id',
            ],
            'wallet_transaction_status_id' => [
                'required',
                'integer',
                'exists:wallet_transaction_statuses,id',
            ],
            'amount' => [
                'required',
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
            'transaction_date' => [
                'nullable',
                'date',
            ],
        ];
    }
}
