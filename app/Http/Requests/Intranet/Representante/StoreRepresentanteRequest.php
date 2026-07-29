<?php

namespace App\Http\Requests\Intranet\Representante;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreRepresentanteRequest extends FormRequest
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
            "rfc" => ['required', 'string', 'min:13', 'max:13', 'unique:representantes,rfc'],
            "telefono" => ['required', 'numeric', 'digits:10', 'unique:representantes,telefono'],
            'email' => ['nullable', 'email', 'unique:representantes,email'],
            'state_entity_id' => ['required', 'integer', 'exists:state_entities,id'],
            'town_id' => ['required', 'integer', 'exists:towns,id'],
            "colonia" => ['required', 'string', 'max:255'],
            "calle" => ['nullable', 'string', 'max:255'],
            "codigo_postal" => ['required', 'numeric', 'digits:5'],
            'cliente_id' => ['required', 'integer', 'exists:clientes,id','unique:representantes,cliente_id'],
        ];
    }

    public function messages(): array
    {
        return[
            'nombre.required' => 'El nombre es requerido',
            'rfc.required' => 'EL rfc es requerido',
            'telefono.required' => 'El teléfono es requerido',
            'state_entity_id.required' => 'Selecciona el estado',
            'town_id.required' => 'Selecciona la ciudad',
            'colonia.required' => 'La colonia es requerida',
            'codigo_postal.required' => 'El código postal es requerido',
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
