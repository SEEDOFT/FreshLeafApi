<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
     * @return array<string, array<int, string|Rule>>
     */
    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'product_category_id' => ['sometimes', 'integer', 'exists:product_categories,id'],
            'product_type_id' => ['sometimes', 'integer', 'exists:product_types,id'],
            'default_unit_id' => ['sometimes', 'integer', 'exists:units,id'],
            'product_status_id' => ['sometimes', 'integer', 'exists:product_statuses,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($productId)],
            'description' => ['sometimes', 'nullable', 'string'],
            'nutrition_data' => ['sometimes', 'nullable', 'array'],
            'shelf_life_days' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ];
    }
}
