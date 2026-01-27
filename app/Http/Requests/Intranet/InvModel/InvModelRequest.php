<?php

namespace App\Http\Requests\Intranet\InvModel;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class InvModelRequest extends FormRequest
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
        // Modelo en la ruta (null en store, objeto en update)
        $item = $this->route('invModel'); // cámbialo al nombre real del parámetro de ruta
        return [
            'code' => ['required', 'string', 'max:255', Rule::unique('inv_models', 'code')->ignore($item?->id),],
            'name' => ['required', 'string', 'max:255', Rule::unique('inv_models', 'name')->ignore($item?->id),],
            'description' => ['nullable', 'string',],
            'price' => ['nullable', 'numeric', 'min:0',],
            'tipo_equipo_id' => ['required', 'integer', 'exists:tipos_equipo,id',],
            'clas_equipo_id' => ['required', 'integer', 'exists:clas_equipos,id',],
            'inv_configurations' => ['present', 'array'],
            'inv_configurations.*' => ['integer', 'exists:inv_configurations,id'],
            'base64' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'El código es obligatorio.',
            'code.string' => 'El código debe ser una cadena de texto.',
            'code.max' => 'El código no debe superar los 255 caracteres.',
            'code.unique' => 'Ya existe un modelo con este código.',

            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no debe superar los 255 caracteres.',
            'name.unique' => 'Ya existe un modelo con este nombre.',

            'description.string' => 'La descripción debe ser una cadena de texto.',

            'price.numeric' => 'El precio debe ser un valor numérico.',
            'price.min' => 'El precio no puede ser menor a 0.',

            'tipo_equipo_id.required' => 'El tipo de equipo es obligatorio.',
            'tipo_equipo_id.integer' => 'El tipo de equipo debe ser un identificador válido.',
            'tipo_equipo_id.exists' => 'El tipo de equipo seleccionado no existe.',

            'clas_equipo_id.required' => 'La clasificación del equipo es obligatoria.',
            'clas_equipo_id.integer' => 'La clasificación del equipo debe ser un identificador válido.',
            'clas_equipo_id.exists' => 'La clasificación del equipo seleccionada no existe.',

            'inv_configurations.present' => 'Debe enviar el campo de configuraciones, aunque esté vacío.',
            'inv_configurations.array' => 'Las configuraciones deben enviarse como un arreglo.',
            'inv_configurations.*.integer' => 'Cada configuración debe ser un identificador válido.',
            'inv_configurations.*.exists' => 'Una de las configuraciones seleccionadas no existe.',

            'base64.string' => 'La imagen debe enviarse en formato base64 válido.',
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
