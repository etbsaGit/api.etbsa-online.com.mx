<?php

namespace App\Http\Requests\OrdenCompra\Vehicle;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AutorizacionServicioRequest extends FormRequest
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
            'orden_servicio_id' => ['required', 'exists:vehicles_ordenes_servicio,id'],
            'autorizado' => ['required', 'boolean'],
            'comentarios' => [
                Rule::requiredIf(function () {
                    return filter_var($this->input('autorizado'), FILTER_VALIDATE_BOOLEAN) === false;
                }),
                'nullable',
                'string',
            ],
            'detalles_ids' => [
                Rule::requiredIf(function () {
                    return filter_var($this->input('autorizado'), FILTER_VALIDATE_BOOLEAN) === true;
                }),
                'nullable',
                'array',
                'min:1',
            ],
            'detalles_ids.*' => [
                'integer',
                'exists:vehicles_detalles_servicio,id',
            ],
        ];
    }

    public function messages()
    {
        return [
            'orden_servicio_id.required' => 'La orden de servicio es obligatoria',
            'orden_servicio_id.exists' => 'La orden de servicio no existe',
            'autorizado.required' => 'La autorización es obligatoria',
            'comentarios.required' => 'El comentario es obligatorio cuando no se autoriza la orden',
            'detalles_ids.required' => 'Debe seleccionar al menos una cotización para autorizar la orden',
            'detalles_ids.min' => 'Debe seleccionar al menos una cotización para autorizar la orden',
            'detalles_ids.array' => 'El formato de cotizaciones seleccionadas es inválido',
            'detalles_ids.*.exists' => 'Una de las cotizaciones seleccionadas no existe',
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
