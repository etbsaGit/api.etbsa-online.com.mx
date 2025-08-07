<?php

namespace App\Http\Requests\Candidato;

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
            "base64" => ['nullable', 'string'],
            "nombre" => ['required', 'string', 'max:255'],
            "descripcion" => ['nullable', 'string'],
            "telefono" => ['nullable', 'numeric', 'digits:10', Rule::unique('candidatos')->ignore($this->route("candidato")->id)],
            "status_1" => ['required', 'string', 'max:255'],
            "fecha_entrevista_1" => ['nullable', 'date'],
            "forma_reclutamiento" => ['required', 'string', 'max:255'],
            "status_2" => ['required', 'string', 'max:255'],
            "fecha_ingreso" => ['nullable', 'date'],
            'requisicion_id' => ['required', 'exists:requisicion_personals,id'],
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
