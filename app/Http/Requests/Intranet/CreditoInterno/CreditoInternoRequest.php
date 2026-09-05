<?php

namespace App\Http\Requests\Intranet\CreditoInterno;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreditoInternoRequest extends FormRequest
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
            'cliente_id' => ['required', 'exists:clientes,id'],
            'notificado_id' => ['required', 'exists:empleados,id'],
            'motivo' => ['required'],
            'monto_solicitado' => ['required', 'numeric'],
            'linea_id' => ['required', 'exists:credito_lineas,id'],
            'numero_pagos' => ['required', 'numeric', 'min:1'],
            'notas' => ['nullable'],
            'anticipo' => ['nullable'],

            // calendario de pagos
            'pagos' => ['nullable', 'array'],
            'pagos.*.numero' => ['nullable', 'numeric'],
            'pagos.*.fecha' => ['nullable', 'date'],

            // archivos
            'archivos'             => ['nullable', 'array'],
            'archivos.*.tipo'      => ['required', 'string'],
            'archivos.*.base64'    => ['required', 'string'],
            'archivos.*.extension' => ['nullable', 'string'],

        ];
    }

    public function messages()
    {
        return [
            'cliente_id.required' => 'El cliente es obligatorio',
            'notificado_id.required' => 'El notificar es obligatorio',
            'motivo.required' => 'El motivo es obligatorio',
            'monto_solicitado.required' => 'El monto solicitado es obligatorio',
            'linea_id.required' => 'La linea es obligatoria',
            'numero_pagos.required' => 'El numero de pagos es obligatorio',

            // calendario de pagos
            'pagos.*.numero.numeric' => 'El numero de pago debe ser un número',
            'pagos.*.fecha.date' => 'La fecha de pago debe ser una fecha',
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
