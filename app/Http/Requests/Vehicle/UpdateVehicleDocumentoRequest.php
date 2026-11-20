<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateVehicleDocumentoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->boolean('es_permanente')) {
            $this->merge([
                'fecha_vencimiento' => null,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $esPermanente = $this->boolean('es_permanente');
        $tipoDoc = $this->input('tipo_documento');

        $vencimientoRules = ['nullable', 'date', 'after_or_equal:fecha_emision'];
        if (in_array($tipoDoc, ['seguro', 'verificacion'], true) || ($tipoDoc === 'tarjeta_circulacion' && !$esPermanente)) {
            $vencimientoRules = ['required', 'date', 'after_or_equal:fecha_emision'];
        }

        return [
            'vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
            'tipo_documento' => [
                'required',
                'string',
                Rule::in(['seguro', 'tarjeta_circulacion', 'verificacion']),
            ],
            'folio_o_poliza' => ['nullable', 'string', 'max:255'],
            'detalle_extra' => ['nullable', 'string', 'max:255'],
            'fecha_emision' => ['required', 'date'],
            'es_permanente' => ['nullable', 'boolean'],
            'fecha_vencimiento' => $vencimientoRules,
            'es_vigente' => ['nullable', 'boolean'],
            'observaciones' => ['nullable', 'string', 'max:1500'],
            'eliminar_archivo' => ['nullable', 'boolean'],
            'archivo' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'base64' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (!empty($value)) {
                        if (!preg_match('/^data:application\/pdf;base64,/i', $value)) {
                            $fail('El archivo adjunto debe estar exclusivamente en formato PDF.');
                        }
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_id.exists' => 'El vehículo seleccionado no existe.',
            'tipo_documento.required' => 'El tipo de documento es obligatorio.',
            'tipo_documento.in' => 'El tipo de documento debe ser Seguro, Tarjeta de Circulación o Verificación.',
            'folio_o_poliza.max' => 'El folio o número de póliza no puede exceder 255 caracteres.',
            'detalle_extra.max' => 'El detalle adicional no puede exceder 255 caracteres.',
            'fecha_emision.required' => 'La fecha de emisión o realización del trámite es obligatoria.',
            'fecha_emision.date' => 'La fecha de emisión no tiene un formato válido.',
            'fecha_vencimiento.required' => 'La fecha de vencimiento o próxima renovación es obligatoria para este documento.',
            'fecha_vencimiento.date' => 'La fecha de vencimiento no tiene un formato válido.',
            'fecha_vencimiento.after_or_equal' => 'La fecha de vencimiento debe ser igual o posterior a la fecha de emisión.',
            'observaciones.max' => 'Las observaciones no pueden exceder 1500 caracteres.',
            'archivo.mimes' => 'Solo se permiten archivos en formato PDF.',
            'archivo.max' => 'El archivo PDF no debe superar los 10 MB.',
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
