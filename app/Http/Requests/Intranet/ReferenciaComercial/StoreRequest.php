<?php

namespace App\Http\Requests\Intranet\ReferenciaComercial;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
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
            "nombre" => ['required', 'string', 'max:255'],
            "telefono" => ['required', 'numeric', 'digits:10'],
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            "negocio" => ['nullable', 'string', 'max:255'],
            "domicilio" => ['required', 'string', 'max:255'],
            "empresa" => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es requerido',
            'telefono.required' => 'El teléfono es requerido',
            'domicilio.required' => 'El domicilio es requerido',
            'empresa.required' => 'La empresa es requerida'
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
