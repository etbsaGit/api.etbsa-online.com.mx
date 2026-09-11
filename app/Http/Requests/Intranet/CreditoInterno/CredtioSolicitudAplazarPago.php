<?php

namespace App\Http\Requests\Intranet\CreditoInterno;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CredtioSolicitudAplazarPago extends FormRequest
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
            'pago_id' => ['required', 'exists:credito_historial_pagos,id'],
            'fecha_actual' => ['required', 'date'],
            'fecha_nueva' => ['required', 'date'],
            'motivo' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'pago_id.required' => 'El pago es obligatorio',
            'fecha_actual.required' => 'La fecha actual es obligatoria',
            'fecha_nueva.required' => 'La fecha nueva es obligatoria',
            'motivo.required' => 'El motivo es obligatorio',
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
