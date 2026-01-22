<?php

namespace App\Http\Requests\Intranet\InvConfiguration;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class StoreRequest extends FormRequest
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
        $item = $this->route('invConfiguration'); // null en store, modelo en update

        return [
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('inv_configurations')
                    ->where(
                        fn($query) => $query
                            ->where('inv_category_id', $this->inv_category_id)
                            ->where('name', $this->name)
                    )
                    ->ignore($item?->id),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
                // sin unique aquí, porque la unicidad real la estamos controlando con el combo
            ],

            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'inv_category_id' => ['required', 'integer', 'exists:inv_categories,id'],
        ];
    }


    public function messages(): array
    {
        return [
            // CODE
            'code.required' => 'El código es obligatorio.',
            'code.string' => 'El código debe ser una cadena de texto.',
            'code.max' => 'El código no puede exceder los 255 caracteres.',
            'code.unique' => 'Ya existe un registro con este código en la categoría seleccionada.',

            // NAME
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no puede exceder los 255 caracteres.',
            'name.unique' => 'Ya existe un registro con este nombre en la categoría seleccionada.',

            // DESCRIPTION
            'description.string' => 'La descripción debe ser una cadena de texto.',

            // PRICE
            'price.numeric' => 'El precio debe ser un valor numérico.',
            'price.min' => 'El precio no puede ser menor a 0.',

            // CATEGORY
            'inv_category_id.required' => 'La categoría es obligatoria.',
            'inv_category_id.integer' => 'La categoría seleccionada no es válida.',
            'inv_category_id.exists' => 'La categoría seleccionada no existe.',
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
