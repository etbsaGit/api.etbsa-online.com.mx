<?php

namespace App\Http\Requests\OrdenCompra\Vehicle;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RevisionServicioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'orden_servicio_id' => ['required', 'integer', 'exists:vehicles_ordenes_servicio,id'],
            'accion' => ['required', 'string', 'in:aprobar,corregir,rechazar'],
            'comentarios' => [
                Rule::requiredIf(function () {
                    return in_array($this->input('accion'), ['corregir', 'rechazar'], true);
                }),
                'nullable',
                'string',
                'max:1500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'orden_servicio_id.required' => 'La orden de compra es obligatoria.',
            'orden_servicio_id.exists' => 'La orden de compra especificada no existe.',
            'accion.required' => 'Debe indicar la acción de revisión.',
            'accion.in' => 'La acción seleccionada no es válida (debe ser aprobar, corregir o rechazar).',
            'comentarios.required' => 'Es obligatorio ingresar las observaciones o motivo para mandar a corregir o rechazar la orden.',
            'comentarios.max' => 'Los comentarios no pueden exceder los 1500 caracteres.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors(),
        ], 422));
    }
}
