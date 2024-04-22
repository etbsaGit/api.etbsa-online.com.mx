<?php

namespace App\Http\Requests\Ecommerce;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|max:191',
            'sku' => 'required|max:191',
            'images' => 'nullable|array',
            'category_id' => 'nullable|array',
            'features' => 'nullable|array',
            'brand_id' => 'nullable',
            'vendor_id' => 'nullable',
            'description' => 'nullable',
            'active' => 'required|boolean',
            'featured' => 'required|boolean',
            'price' => 'required|decimal:2',
            'sale_price' => 'required|decimal:2',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'data' => $validator->errors()
        ]));
    }
}
