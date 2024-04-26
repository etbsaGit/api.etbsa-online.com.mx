<?php

namespace App\Http\Requests\Ecommerce;

use Illuminate\Support\Str;
use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $name = $this->input('name');
        $slug = Str::slug($name);
        $this->merge([
            'slug' => $slug,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required','string','max:191','unique:products,name'],
            'sku' => ['required','string','max:191','unique:products,sku'],
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
            'quantity'=>'nullable|decimal:0'
        ];
    }

    function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            $response = new Response($validator->errors(), 422);
            throw new ValidationException($validator, $response);
        }
    }
}
