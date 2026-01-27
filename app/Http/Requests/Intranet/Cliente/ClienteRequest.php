<?php

namespace App\Http\Requests\Intranet\Cliente;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ClienteRequest extends FormRequest
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
        $item = $this->route('cliente');
        return [
            "equip" => ['nullable', 'numeric', 'digits:5', Rule::unique('clientes')->ignore($item?->id)],
            "nombre" => ['required', 'string', 'max:255'],
            'tipo' => ['required', 'in:moral,fisica'],
            "rfc" => ['required', 'string', 'min:12', 'max:13', Rule::unique('clientes')->ignore($item?->id)],
            "curp" => ['nullable', 'string', 'min:18', 'max:18', Rule::unique('clientes')->ignore($item?->id)],
            "telefono" => ['required', 'numeric', 'digits:10', Rule::unique('clientes')->ignore($item?->id)],
            "telefono_casa" => ['nullable', 'numeric', 'digits:10', Rule::unique('clientes')->ignore($item?->id)],
            'email' => ['nullable', 'email', Rule::unique('clientes')->ignore($item?->id)],

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

    public function messages(): array
    {
        return [
            // equip
            'equip.numeric' => 'El campo equipo debe ser numérico.',
            'equip.digits' => 'El equipo debe contener exactamente 5 dígitos.',
            'equip.unique' => 'El número de equipo ya está registrado.',

            // nombre
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser texto.',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres.',

            // tipo
            'tipo.required' => 'El tipo de cliente es obligatorio.',
            'tipo.in' => 'El tipo debe ser "moral" o "fisica".',

            // rfc
            'rfc.required' => 'El RFC es obligatorio.',
            'rfc.string' => 'El RFC debe ser texto.',
            'rfc.min' => 'El RFC debe tener al menos 12 caracteres.',
            'rfc.max' => 'El RFC no puede tener más de 13 caracteres.',
            'rfc.unique' => 'El RFC ya está registrado.',

            // curp
            'curp.string' => 'La CURP debe ser texto.',
            'curp.min' => 'La CURP debe tener 18 caracteres.',
            'curp.max' => 'La CURP debe tener 18 caracteres.',
            'curp.unique' => 'La CURP ya está registrada.',

            // telefono
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.numeric' => 'El teléfono debe ser numérico.',
            'telefono.digits' => 'El teléfono debe contener exactamente 10 dígitos.',
            'telefono.unique' => 'El teléfono ya está registrado.',

            // telefono_casa
            'telefono_casa.numeric' => 'El teléfono de casa debe ser numérico.',
            'telefono_casa.digits' => 'El teléfono de casa debe contener exactamente 10 dígitos.',
            'telefono_casa.unique' => 'El teléfono de casa ya está registrado.',

            // email
            'email.email' => 'El correo electrónico no tiene un formato válido.',
            'email.unique' => 'El correo electrónico ya está registrado.',

            // ubicación
            'state_entity_id.required' => 'El estado es obligatorio.',
            'state_entity_id.integer' => 'El estado seleccionado no es válido.',
            'state_entity_id.exists' => 'El estado seleccionado no existe.',

            'town_id.required' => 'El municipio es obligatorio.',
            'town_id.integer' => 'El municipio seleccionado no es válido.',
            'town_id.exists' => 'El municipio seleccionado no existe.',

            'colonia.required' => 'La colonia es obligatoria.',
            'colonia.string' => 'La colonia debe ser texto.',
            'colonia.max' => 'La colonia no puede exceder 255 caracteres.',

            'calle.string' => 'La calle debe ser texto.',
            'calle.max' => 'La calle no puede exceder 255 caracteres.',

            'codigo_postal.required' => 'El código postal es obligatorio.',
            'codigo_postal.numeric' => 'El código postal debe ser numérico.',
            'codigo_postal.digits' => 'El código postal debe contener exactamente 5 dígitos.',

            // clasificaciones
            'classification_id.integer' => 'La clasificación seleccionada no es válida.',
            'classification_id.exists' => 'La clasificación seleccionada no existe.',

            'segmentation_id.integer' => 'La segmentación seleccionada no es válida.',
            'segmentation_id.exists' => 'La segmentación seleccionada no existe.',

            'tactic_id.integer' => 'La táctica seleccionada no es válida.',
            'tactic_id.exists' => 'La táctica seleccionada no existe.',

            'construction_classification_id.integer' => 'La clasificación de construcción no es válida.',
            'construction_classification_id.exists' => 'La clasificación de construcción no existe.',
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
