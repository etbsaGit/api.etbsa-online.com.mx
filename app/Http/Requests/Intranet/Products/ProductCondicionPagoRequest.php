<?php

namespace App\Http\Requests\Intranet\Products;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductCondicionPagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:191',
                Rule::unique('products_condicion_pago', 'name')->ignore($this->id)
            ],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['exists:categories,id'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre es obligatorio',
            'category_ids.array' => 'Las categorías deben ser un arreglo',
            'name.unique'   => 'Ya existe una condición de pago con este nombre',
            'category_ids.*.exists' => 'Una categoría no existe'
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
