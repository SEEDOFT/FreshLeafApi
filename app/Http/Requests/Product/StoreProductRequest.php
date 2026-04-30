<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'product_category_id' => ['required', 'integer', 'exists:product_categories,id'],
            'product_type_id' => ['required', 'integer', 'exists:product_types,id'],
            'default_unit_id' => ['required', 'integer', 'exists:units,id'],
            'product_status_id' => ['required', 'integer', 'exists:product_statuses,id'],
            'vendor_user_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'name_en' => ['required', 'string', 'max:255'],
            'name_km' => ['sometimes', 'nullable', 'string', 'max:255'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255', 'unique:products,slug'],
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
