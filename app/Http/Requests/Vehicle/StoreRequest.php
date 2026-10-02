<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Http\Response;
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
        return [
            'placas' => ['required', 'string', 'unique:vehicles'],
            'serie' => ['required', 'string', 'unique:vehicles'],
            'departamento_id' => ['required', 'integer', 'exists:departamentos,id'],
            'linea_id' => ['required', 'integer', 'exists:lineas,id'],
            'sucursal_id' => ['required', 'integer', 'exists:sucursales,id'],
            'estatus_id' => ['required', 'integer', 'exists:estatus,id'],
            'activo' => ['nullable', 'boolean'],
            'motivo_baja' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'placas.required' => 'La placa es obligatoria',
            'placas.unique' => 'La placa ya existe',
            'serie.required' => 'La serie es obligatoria',
            'serie.unique' => 'La serie ya existe',
            'departamento_id.required' => 'El departamento es obligatorio',
            'linea_id.required' => 'La linea es obligatoria',
            'sucursal_id.required' => 'La sucursal es obligatoria',
            'estatus_id.required' => 'El estatus es obligatorio',
            'activo.required' => 'El activo es obligatorio',
            'motivo_baja.required' => 'El motivo de baja es obligatorio',
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
