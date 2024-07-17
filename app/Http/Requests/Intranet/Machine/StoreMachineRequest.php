<?php

namespace App\Http\Requests\Intranet\Machine;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreMachineRequest extends FormRequest
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
            "serie" => ['required', 'string', 'max:255'],
            "modelo" => ['required', 'string', 'max:255'],
            "anio" => ['required', 'numeric', 'digits:4'],
            "valor" => ['required', 'numeric'],
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'marca_id' => ['required', 'integer', 'exists:marcas,id'],
            'condicion_id' => ['required', 'integer', 'exists:condiciones,id'],
            'clas_equipo_id' => ['required', 'integer', 'exists:clas_equipos,id'],
            'tipo_equipo_id' => ['required', 'integer', 'exists:tipos_equipo,id'],
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
