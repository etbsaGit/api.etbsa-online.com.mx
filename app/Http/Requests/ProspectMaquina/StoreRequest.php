<?php

namespace App\Http\Requests\ProspectMaquina;

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
            "modelo" => ['nullable', 'string', 'max:255'],
            "anio" => ['nullable', 'numeric', 'digits:4'],
            'prospect_id' => ['required', 'integer', 'exists:prospects,id'],
            'marca_id' => ['required', 'integer', 'exists:marcas,id'],
            'condicion_id' => ['required', 'integer', 'exists:condiciones,id'],
            'clas_equipo_id' => ['nullable', 'integer', 'exists:clas_equipos,id'],
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
