<?php

namespace App\Http\Requests\Intranet\Products;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductRiegoRequest extends FormRequest
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

            'sku'            => ['required', 'string', 'max:100'],
            'name'           => ['required', 'string', 'max:191'],
            'description'    => ['nullable', 'string'],

            'active'         => ['required', 'boolean'],

            'precios' => ['nullable', 'array'],
            'precios.*.id' => ['nullable', 'exists:precio_prod_riego,id'],
            'precios.*.nivel_partner_id' => ['required', 'exists:nivel_partner,id'],
            'precios.*.precio' => ['required', 'numeric'],

            'imagenes'   => ['nullable', 'array'],
            'imagenes.*' => ['nullable', 'string'],

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

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Errores de validación',
            'errors'  => $validator->errors()
        ], 422));
    }
}
