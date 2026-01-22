<?php

namespace App\Http\Requests\Intranet\Cliente;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreClienteRequest extends FormRequest
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
            "equip" => ['nullable', 'numeric', 'digits:5', 'unique:clientes,equip'],
            "nombre" => ['required', 'string', 'max:255'],
            'tipo' => ['required', 'in:moral,fisica'],
            "rfc" => ['required', 'string', 'min:12', 'max:13', 'unique:clientes,rfc'],
            "curp" => ['nullable', 'string', 'min:18', 'max:18', 'unique:clientes,curp'],
            "telefono" => ['required', 'numeric', 'digits:10', 'unique:clientes,telefono'],
            "telefono_casa" => ['nullable', 'numeric', 'digits:10', 'unique:clientes,telefono_casa'],
            'email' => ['nullable', 'email', 'unique:clientes,email'],

            'state_entity_id' => ['required', 'integer', 'exists:state_entities,id'],
            'town_id' => ['required', 'integer', 'exists:towns,id'],
            "colonia" => ['required', 'string', 'max:255'],
            "calle" => ['nullable', 'string', 'max:255'],
            "codigo_postal" => ['required', 'numeric', 'digits:5'],

            'classification_id' => ['nullable', 'integer', 'exists:classifications,id'],
            'segmentation_id' => ['nullable', 'integer', 'exists:segmentations,id'],
            'tactic_id' => ['nullable', 'integer', 'exists:tactics,id'],
            'construction_classification_id' => ['nullable', 'integer', 'exists:construction_classifications,id'],
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
