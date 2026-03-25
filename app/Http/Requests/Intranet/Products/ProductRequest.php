<?php

namespace App\Http\Requests\Intranet\Products;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'brand_id'       => ['nullable', 'exists:brands,id'],
            'vendor_id'      => ['nullable', 'exists:vendors,id'],
            'category_id'    => ['required', 'exists:categories,id'],
            'subcategory_id' => ['required', 'exists:product_subcategory,id'],
            'currency_id'    => ['required', 'exists:currency,id'],
            'agency_id'      => ['required', 'exists:sucursales,id'],

            'sku'            => ['required', 'string', 'max:100'],
            'name'           => ['required', 'string', 'max:191'],
            'description'    => ['nullable', 'string'],

            'active'         => ['required', 'boolean'],
            'is_usado'       => ['required', 'boolean'],
            'is_dollar'      => ['required', 'boolean'],

            // precios
            'price_1'  => ['nullable', 'numeric'],
            'price_2'  => ['nullable', 'numeric'],
            'price_3'  => ['nullable', 'numeric'],
            'price_4'  => ['nullable', 'numeric'],
            'price_5'  => ['nullable', 'numeric'],
            'price_6'  => ['nullable', 'numeric'],
            'price_7'  => ['nullable', 'numeric'],
            'price_8'  => ['nullable', 'numeric'],
            'price_9'  => ['nullable', 'numeric'],
            'price_10' => ['nullable', 'numeric'],
            'price_11' => ['nullable', 'numeric'],
            'price_12' => ['nullable', 'numeric'],
            'price_13' => ['nullable', 'numeric'],
            'price_14' => ['nullable', 'numeric'],
        ];
    }

    public function messages(): array
    {
        return [
            'brand_id.required' => 'La marca es obligatoria',
            'brand_id.exists'   => 'La marca no es válida',

            'category_id.required' => 'La categoría es obligatoria',

            'sku.required' => 'El SKU es obligatorio',
            'sku.unique' => 'El SKU ya existe',
            'name.required' => 'El nombre es obligatorio',
            'description.required' => 'La descripción es obligatoria',

            'active.required' => 'El estado es obligatorio',
            'active.boolean' => 'El estado debe ser verdadero o falso',
        ];
    }
}
