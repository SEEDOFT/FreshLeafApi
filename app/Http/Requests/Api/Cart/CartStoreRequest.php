<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Cart;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

use function auth;

class CartStoreRequest extends FormRequest
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
            'vendor_inventory_id' => ['required', 'integer', 'exists:vendor_inventories,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
