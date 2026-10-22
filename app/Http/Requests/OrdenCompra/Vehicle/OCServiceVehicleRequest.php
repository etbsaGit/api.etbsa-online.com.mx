<?php

namespace App\Http\Requests\OrdenCompra\Vehicle;

use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class OCServiceVehicleRequest extends FormRequest
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
        $serviceParam = $this->route('service');
        $ignoreOrderId = is_object($serviceParam) ? $serviceParam->id : $serviceParam;
        $vehicleId = $this->input('vehicle_id');

        $ultimoKm = 0;
        if (!empty($vehicleId)) {
            $query = DB::table('vehicles_detalles_servicio')
                ->join('vehicles_ordenes_servicio', 'vehicles_detalles_servicio.orden_servicio_id', '=', 'vehicles_ordenes_servicio.id')
                ->where('vehicles_ordenes_servicio.vehicle_id', $vehicleId);

            if (!empty($ignoreOrderId)) {
                $query->where('vehicles_ordenes_servicio.id', '!=', $ignoreOrderId);
            }

            $ultimoKm = (int) $query->max('vehicles_detalles_servicio.km_evento');
        }

        return [
            'autorizacion_compras' => ['nullable', 'string', 'max:255'],
            'orden_compra' => ['nullable', 'string', 'max:255'],
            'fecha_elaboracion' => ['required', 'date'],
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'proveedor_id' => ['required', 'integer', 'exists:vehicles_servicio_proveedores,id'],
            'solicitante_id' => ['nullable', 'integer', 'exists:empleados,id'],
            'suc_cargo_id' => ['nullable', 'integer', 'exists:vehicles_suc_cargo_a,id'],
            'depto_cargo_id' => ['nullable', 'integer', 'exists:vehicles_depto_cargo_a,id'],
            'cotizacion_url' => ['nullable', 'string'],
            'archivo' => ['nullable'],
            'base64' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (!empty($value)) {
                        if (!preg_match('/^data:application\/pdf;base64,/', $value)) {
                            $fail('El archivo de cotización debe ser exclusivamente en formato PDF.');
                        }
                    }
                },
            ],

            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.servicio_id' => ['required', 'integer', 'exists:vehicles_servicios_cat,id'],
            'detalles.*.vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
            'detalles.*.km_evento' => [
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
            'detalles.*.descripcion' => ['required', 'string'],
            'detalles.*.precio_unitario' => ['nullable', 'numeric', 'min:0'],
            'detalles.*.cantidad' => ['nullable', 'integer', 'min:1'],
            'detalles.*.llanta_anchura' => ['nullable', 'integer', 'min:1'],
            'detalles.*.llanta_altura' => ['nullable', 'integer', 'min:1'],
            'detalles.*.llanta_rin' => ['nullable', 'integer', 'min:1'],
            'detalles.*.subtotal' => ['required', 'numeric', 'min:0'],
            'detalles.*.iva' => ['required', 'numeric', 'min:0'],
            'detalles.*.total' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages()
    {
        return [
            'autorizacion_compras.required' => 'El número de autorización de compras es obligatorio',
            'autorizacion_compras.max' => 'El número de autorización no puede exceder 255 caracteres',
            'fecha_elaboracion.required' => 'La fecha de elaboración del servicio es obligatoria',
            'fecha_elaboracion.date' => 'La fecha de elaboración no tiene un formato válido',
            'vehicle_id.required' => 'La unidad / vehículo es obligatoria',
            'vehicle_id.exists' => 'La unidad seleccionada no existe',
            'proveedor_id.required' => 'El proveedor es obligatorio',
            'proveedor_id.exists' => 'El proveedor seleccionado no existe',
            'solicitante_id.exists' => 'El empleado solicitante seleccionado no existe',
            'suc_cargo_id.exists' => 'La sucursal con cargo seleccionada no existe',
            'depto_cargo_id.exists' => 'El departamento con cargo seleccionado no existe',

            'detalles.required' => 'Debes agregar al menos un servicio a la orden',
            'detalles.array' => 'El formato de los detalles de servicio no es válido',
            'detalles.min' => 'Debes registrar al menos un servicio en la orden',
            'detalles.*.servicio_id.required' => 'El servicio realizado es obligatorio en cada concepto',
            'detalles.*.servicio_id.exists' => 'El servicio seleccionado no existe en el catálogo',
            'detalles.*.km_evento.required' => 'El kilometraje es obligatorio',
            'detalles.*.km_evento.numeric' => 'El kilometraje debe ser un valor numérico',
            'detalles.*.km_evento.min' => 'El kilometraje no puede ser negativo',
            'detalles.*.descripcion.required' => 'La descripción del servicio es obligatoria',
            'detalles.*.precio_unitario.numeric' => 'El precio unitario debe ser numérico',
            'detalles.*.cantidad.integer' => 'La cantidad debe ser un número entero',
            'detalles.*.cantidad.min' => 'La cantidad debe ser al menos 1',
            'detalles.*.llanta_anchura.integer' => 'La anchura de la llanta debe ser un número entero',
            'detalles.*.llanta_altura.integer' => 'La altura de la llanta debe ser un número entero',
            'detalles.*.llanta_rin.integer' => 'El rin de la llanta debe ser un número entero',
            'detalles.*.subtotal.required' => 'El subtotal es obligatorio',
            'detalles.*.subtotal.numeric' => 'El subtotal debe ser numérico',
            'detalles.*.iva.required' => 'El IVA es obligatorio',
            'detalles.*.iva.numeric' => 'El IVA debe ser numérico',
            'detalles.*.total.required' => 'El total es obligatorio',
            'detalles.*.total.numeric' => 'El total debe ser numérico',
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
