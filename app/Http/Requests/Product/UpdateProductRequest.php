<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
        $productId = $this->route('id');

        return [
            'product_category_id' => ['sometimes', 'integer', 'exists:product_categories,id'],
            'product_type_id' => ['sometimes', 'integer', 'exists:product_types,id'],
            'default_unit_id' => ['sometimes', 'integer', 'exists:units,id'],
            'product_status_id' => ['sometimes', 'integer', 'exists:product_statuses,id'],
            'name_en' => ['sometimes', 'string', 'max:255'],
            'name_km' => ['sometimes', 'nullable', 'string', 'max:255'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($productId)],
            'description_en' => ['sometimes', 'nullable', 'string'],
            'description_km' => ['sometimes', 'nullable', 'string'],
            'selling_unit' => ['sometimes', 'nullable', 'string', 'max:50'],
            'price_per_unit' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'available_stock' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'farm_name_location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'farming_method' => ['sometimes', 'nullable', 'string', 'in:certified_organic,pesticide_free,naturally_grown'],
            'harvest_date' => ['sometimes', 'nullable', 'date'],
            'is_active' => ['sometimes', 'boolean'],
            'is_organic' => ['sometimes', 'boolean'],
            'nutrition_data' => ['sometimes', 'nullable', 'array'],
            'shelf_life_days' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ];
    }
}
