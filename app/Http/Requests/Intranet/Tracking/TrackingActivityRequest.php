<?php

namespace App\Http\Requests\Intranet\Tracking;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TrackingActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tracking_id' => ['required', 'exists:tracking,id'],
            'tipo_seguimiento_id' => ['required', 'exists:tracking_tipo_seguimientoc'],
            'certeza_id' => ['required', 'exists:tracking_certeza'],
            'ultimo_precio_tratar' => ['required', 'numeric'],
            'tarifa_cambio' => ['required', 'numeric'],
            'currency_id' => ['required', 'exists:currency,id'],
            'notas' => ['nullable'],
            'date_next_tracking' => ['nullable', 'date']
        ];
    }

    public function messages(): array
    {
        return [
            'tracking_id.required' => 'El campo tracking es obligatorio.',
            'tracking_id.exists' => 'El tracking seleccionado no existe.',
            'tipo_seguimiento_id.required' => 'El campo tipo de seguimiento es obligatorio.',
            'tipo_seguimiento_id.exists' => 'El tipo de seguimiento seleccionado no existe.',
            'certeza_id.required' => 'El campo certeza es obligatorio.',
            'certeza_id.exists' => 'La certeza seleccionada no existe.',
            'ultimo_precio_tratar.required' => 'El campo último precio a tratar es obligatorio.',
            'ultimo_precio_tratar.numeric' => 'El campo último precio a tratar debe ser un número.',
            'tarifa_cambio.required' => 'El campo tarifa de cambio es obligatorio.',
            'tarifa_cambio.numeric' => 'El campo tarifa de cambio debe ser un número.',
            'currency_id.required' => 'El campo moneda es obligatorio.',
            'currency_id.exists' => 'La moneda seleccionada no existe.',
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
