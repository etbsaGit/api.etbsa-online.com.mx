<?php

namespace App\Http\Requests\ExperienciaLaboral;

use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class PutRequest extends FormRequest
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
            "lugar" => ['required', 'string', 'max:255'],
            "inicio" => ['required', 'date'],
            "termino" => ['required', 'date'],
            "telefono" => ['required', 'numeric','digits:10', Rule::unique('experiencias_laborales')->ignore($this->route("experienciaLaboral")->id)],
            "puesto_id" => ['required', 'integer'],
            "direccion_id" => ['required', Rule::unique('experiencias_laborales')->ignore($this->route("experienciaLaboral")->id)],
            "empleado_id" => ['required', 'integer'],

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
