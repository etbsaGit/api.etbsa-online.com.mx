<?php

namespace App\Http\Requests\CreditoDeclaracion;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

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
            "giro" => ['required', 'string', 'max:255'],
            "status" => ['nullable', 'boolean'],
            "inicio"  => ['required', 'date'],
            "termino" => ['required', 'date'],
            "feedback" => ['nullable', 'string'],


            "cliente_id" => ['required', 'exists:clientes,id'],

            // Validación para relaciones (array de objetos)
            "relaciones" => ['required', 'array', 'min:1'],
            "relaciones.*.credito_declaracion_id" => ['nullable', 'integer', 'exists:credito_declaracions,id'],
            "relaciones.*.credito_concepto_id" => ['required', 'integer', 'exists:credito_conceptos,id'],
            "relaciones.*.valor" => ['required', 'numeric'], // si siempre es numérico, si no cambia a 'string'
            "relaciones.*.nombre" => ['required', 'string'], // si siempre es numérico, si no cambia a 'string'
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
