<?php

namespace App\Http\Requests\Intranet\Cliente;

use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class PutClienteRequest extends FormRequest
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
            "equip" => ['nullable', 'numeric', 'digits:5', Rule::unique('clientes')->ignore($this->route("cliente")->id)],
            "nombre" => ['required', 'string', 'max:255'],
            'tipo' => ['required', 'in:moral,fisica'],
            "rfc" => ['required', 'string', 'min:13', 'max:13', Rule::unique('clientes')->ignore($this->route("cliente")->id)],
            "curp" => ['nullable', 'string', 'min:18', 'max:18', Rule::unique('clientes')->ignore($this->route("cliente")->id)],
            "telefono" => ['required', 'numeric', 'digits:10', Rule::unique('clientes')->ignore($this->route("cliente")->id)],
            "telefono_casa" => ['nullable', 'numeric', 'digits:10', Rule::unique('clientes')->ignore($this->route("cliente")->id)],
            'email' => ['nullable', 'email', Rule::unique('clientes')->ignore($this->route("cliente")->id)],

            'state_entity_id' => ['required', 'integer', 'exists:state_entities,id'],
            'town_id' => ['required', 'integer', 'exists:towns,id'],
            "colonia" => ['required', 'string', 'max:255'],
            "calle" => ['nullable', 'string', 'max:255'],
            "codigo_postal" => ['required', 'numeric', 'digits:5'],

            'classification_id' => ['nullable', 'integer', 'exists:classifications,id'],
            'segmentation_id' => ['nullable', 'integer', 'exists:segmentations,id'],
            'technological_capability_id' => ['nullable', 'integer', 'exists:technological_capabilities,id'],
            'tactic_id' => ['nullable', 'integer', 'exists:tactics,id'],
            'construction_classification_id' => ['nullable', 'integer', 'exists:construction_classifications,id'],
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
