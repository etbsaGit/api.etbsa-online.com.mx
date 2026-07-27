<?php

namespace App\Http\Requests\Intranet\ClienteAbastecimiento;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreClienteAbastecimientoRequest extends FormRequest
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
            "cantidad" => ['integer', 'required'],
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'abastecimiento_id' => ['required', 'integer', 'exists:abastecimientos,id'],
        ];
    }

    public function messages(): array{
        return [
            'cantidad.required' => 'Se debe especificar la cantidad',
            'cantidad.integer' => 'La cantidad debe ser un número entero',
            'abastecimiento_id.required' => 'Selecciona el tipo de abastecimiento'
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
