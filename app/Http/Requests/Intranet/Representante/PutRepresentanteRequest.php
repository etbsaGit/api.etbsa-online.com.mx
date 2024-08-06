<?php

namespace App\Http\Requests\Intranet\Representante;

use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class PutRepresentanteRequest extends FormRequest
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
            "rfc" => ['required', 'string', 'min:13', 'max:13', Rule::unique('representantes')->ignore($this->route("representante")->id)],
            "telefono" => ['required', 'numeric', 'digits:10', Rule::unique('representantes')->ignore($this->route("representante")->id)],
            'email' => ['nullable', 'email', Rule::unique('representantes')->ignore($this->route("representante")->id)],
            'state_entity_id' => ['required', 'integer', 'exists:state_entities,id'],
            'town_id' => ['required', 'integer', 'exists:towns,id'],
            "colonia" => ['required', 'string', 'max:255'],
            "calle" => ['nullable', 'string', 'max:255'],
            "codigo_postal" => ['required', 'numeric', 'digits:5'],
            'cliente_id' => ['required', 'integer', 'exists:clientes,id', Rule::unique('representantes')->ignore($this->route("representante")->id)],
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
