<?php

namespace App\Http\Requests\OrdenCompra\Vehicle;

use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class OCGasolinaVehicleRequest extends FormRequest
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
        $primerDetalle = is_array($this->input('detalles')) && !empty($this->input('detalles')[0])
            ? $this->input('detalles')[0]
            : [];

        $this->merge([
            'solicitado_por' => $this->input('solicitado_por') ?? $this->input('solicitante_id') ?? null,
            'lts_solicitados' => $this->input('lts_solicitados') ?? $this->input('cantidad') ?? ($primerDetalle['cantidad'] ?? null),
            'km_evento' => $this->input('km_evento') ?? ($primerDetalle['km_evento'] ?? null),
            'notas' => $this->input('notas') ?? $this->input('descripcion') ?? ($primerDetalle['descripcion'] ?? null),
            'is_urgente' => filter_var($this->input('is_urgente', false), FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    public function rules(): array
    {
        $gasolinaParam = $this->route('gasolina') ?? $this->route('service') ?? $this->route('id');
        $ignoreId = is_object($gasolinaParam) ? $gasolinaParam->id : $gasolinaParam;
        $vehicleId = $this->input('vehicle_id');

        $ultimoKm = 0;
        if (!empty($vehicleId)) {
            $queryGas = DB::table('vehicles_dispersion_gasolina')
                ->where('vehicle_id', $vehicleId);

            if (!empty($ignoreId)) {
                $queryGas->where('id', '!=', $ignoreId);
            }

            $ultimoKmGasolina = (int) $queryGas->max('km_evento');

            $ultimoKmServicios = (int) DB::table('vehicles_detalles_servicio')
                ->join('vehicles_ordenes_servicio', 'vehicles_detalles_servicio.orden_servicio_id', '=', 'vehicles_ordenes_servicio.id')
                ->where('vehicles_ordenes_servicio.vehicle_id', $vehicleId)
                ->max('vehicles_detalles_servicio.km_evento');

            $ultimoKm = max($ultimoKmGasolina, $ultimoKmServicios);
        }

        return [
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'solicitado_por' => ['nullable', 'integer', 'exists:empleados,id'],
            'suc_cargo_id' => ['nullable', 'integer', 'exists:vehicles_suc_cargo_a,id'],
            'depto_cargo_id' => ['nullable', 'integer', 'exists:vehicles_depto_cargo_a,id'],
            'lts_solicitados' => ['required', 'integer', 'min:1'],
            'lts_dispersados' => ['nullable', 'integer', 'min:0'],
            'km_evento' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($ultimoKm) {
                    if ($value !== null && $value !== '' && $ultimoKm > 0 && (int) $value < $ultimoKm) {
                        $fail(
                            'El kilometraje ingresado (' . number_format((int) $value) . ' km) no puede ser menor al último kilometraje registrado de la unidad (' . number_format($ultimoKm) . ' km).'
                        );
                    }
                },
            ],
            'notas' => ['nullable', 'required_if:is_urgente,true', 'string'],
            'estatus_id' => ['nullable', 'integer', 'exists:estatus,id'],
            'is_urgente' => ['nullable', 'boolean'],
        ];
    }

    public function messages()
    {
        return [
            'vehicle_id.required' => 'La unidad / vehículo es obligatoria',
            'vehicle_id.exists' => 'La unidad seleccionada no existe',
            'solicitado_por.exists' => 'El empleado solicitante seleccionado no existe',
            'suc_cargo_id.exists' => 'La sucursal con cargo seleccionada no existe',
            'depto_cargo_id.exists' => 'El departamento con cargo seleccionado no existe',
            'lts_solicitados.required' => 'Los litros solicitados son obligatorios',
            'lts_solicitados.integer' => 'Los litros solicitados deben ser un número entero',
            'lts_solicitados.min' => 'Los litros solicitados deben ser al menos 1',
            'lts_dispersados.integer' => 'Los litros dispersados deben ser un número entero',
            'lts_dispersados.min' => 'Los litros dispersados no pueden ser negativos',
            'km_evento.required' => 'El kilometraje es obligatorio',
            'km_evento.numeric' => 'El kilometraje debe ser un valor numérico',
            'km_evento.min' => 'El kilometraje no puede ser negativo',
            'notas.required_if' => 'Las notas u observaciones son obligatorias cuando la dispersión es urgente',
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
