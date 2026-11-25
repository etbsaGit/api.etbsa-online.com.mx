<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreVehicleChecklistRequest extends FormRequest
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
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'notas' => ['nullable', 'string', 'max:255'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.documento' => ['required', 'string', 'max:255'],
            'detalles.*.existencia' => ['required', 'boolean'],
        ];
    }

    /**
     * Validación adicional: si al menos un documento se marca como no existente en físico (existencia = false),
     * el campo notas es obligatorio.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $detalles = $this->input('detalles', []);
            if (is_array($detalles)) {
                $hasMissing = collect($detalles)->contains(function ($det) {
                    return isset($det['existencia']) && !filter_var($det['existencia'], FILTER_VALIDATE_BOOLEAN);
                });

                if ($hasMissing && empty(trim((string) $this->input('notas', '')))) {
                    $validator->errors()->add(
                        'notas',
                        'Es obligatorio dejar un comentario describiendo por qué no se tiene el documento en físico.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'vehicle_id.required' => 'Debes seleccionar el vehículo a revisar.',
            'vehicle_id.integer' => 'El identificador del vehículo debe ser un número entero.',
            'vehicle_id.exists' => 'El vehículo seleccionado no es válido.',
            'notas.max' => 'Las notas no deben exceder los 255 caracteres.',
            'detalles.required' => 'Debes completar el checklist de documentos.',
            'detalles.array' => 'El formato del checklist de documentos no es válido.',
            'detalles.min' => 'El checklist debe contener al menos un documento.',
            'detalles.*.documento.required' => 'El nombre del documento en el checklist es obligatorio.',
            'detalles.*.documento.max' => 'El nombre del documento no debe exceder 255 caracteres.',
            'detalles.*.existencia.required' => 'Debes indicar si el documento existe en físico o no.',
            'detalles.*.existencia.boolean' => 'El valor de existencia del documento debe ser verdadero o falso.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Errores de validación',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
