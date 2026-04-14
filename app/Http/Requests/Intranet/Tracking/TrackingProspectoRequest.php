<?php

namespace App\Http\Requests\Intranet\Tracking;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TrackingProspectoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required'],
            'email' => ['nullable'],
            'telefono' => ['required','numeric', Rule::unique('tracking_prospectos')],
            'telefono_casa' => ['nullable','numeric'],
            'ubicacion' => ['required']
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio',
            'telefono.required' => 'El teléfono es obligatorio',
            'telefono.numeric' => 'El teléfono debe ser numérico',
            'telefono_casa.numeric' => 'El teléfono fijo debe ser numérico',
            'ubicacion.required' => 'La ubicación es obligatoria',
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
