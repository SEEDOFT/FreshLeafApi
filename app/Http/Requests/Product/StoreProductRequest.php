<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'product_category_id' => ['required', 'integer', 'exists:product_categories,id'],
            'product_type_id' => ['required', 'integer', 'exists:product_types,id'],
            'default_unit_id' => ['required', 'integer', 'exists:units,id'],
            'product_status_id' => ['required', 'integer', 'exists:product_statuses,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255', 'unique:products,slug'],
            'description' => ['sometimes', 'nullable', 'string'],
            'nutrition_data' => ['sometimes', 'nullable', 'array'],
            'shelf_life_days' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ];
    }
}
