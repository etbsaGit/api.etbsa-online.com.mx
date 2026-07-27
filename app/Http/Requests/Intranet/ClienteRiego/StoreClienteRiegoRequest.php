<?php

namespace App\Http\Requests\Intranet\ClienteRiego;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreClienteRiegoRequest extends FormRequest
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
            "hectareas_propias" => ['integer','nullable'],
            "hectareas_rentadas" => ['integer','nullable'],
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'riego_id' => ['required', 'integer', 'exists:riegos,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'riego_id.required' => 'Selecciona el tipo de riego',
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
