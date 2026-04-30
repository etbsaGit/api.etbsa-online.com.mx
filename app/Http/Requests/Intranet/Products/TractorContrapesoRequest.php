<?php

namespace App\Http\Requests\Intranet\Products;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TractorContrapesoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('tractor_contrapeso');
        return [
            'nro_parte' => ['required', 'string', Rule::unique('contrapesos', 'nro_parte')->ignore($id)],
            'descripcion' => ['required'],
            'trasero_delantero' => ['required'],
            'costo' => ['required', 'numeric'],
            'precio' => ['required', 'numeric'],
            'currency_id' => ['required','exists:currency,id'],

            'tractores' => ['array', 'nullable'],
            'tractores.*.id' => ['exists:products,id'],
        ];
    }

    public function messages()
    {
        return [
            'nro_parte.unique' => 'El número de parte ya está registrado',
            'nro_parte.required' => 'El número de parte es obligatorio',
            'descripcion.required' => 'La descripción del contrapeso es obligatorio',
            'trasero_delantero.required' => 'Especifica si el contrapeso es delantero o trasero',
            'costo.required' => 'El costo es requerido',
            'currency_id.required' => 'La moneda es obligatoria',
            'precio.required' => 'El precio es requerido',
            'tractores.*.id.exists' => 'El tractor no existe',
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
