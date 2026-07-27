<?php

namespace App\Http\Requests\Intranet\Distribucion;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreDistribucionRequest extends FormRequest
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
            "ubicacion" => ['required', 'string', 'max:255'],
            "hectareas_propias" => ['integer','nullable'],
            "hectareas_rentadas" => ['integer','nullable'],
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
        ];
    }

    public function messages ():array{
        return [
            'nombre.required' => ' Se debe especificar el nombre',
            'ubicacion.required' => 'Se debe especificar la ubicación',
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
