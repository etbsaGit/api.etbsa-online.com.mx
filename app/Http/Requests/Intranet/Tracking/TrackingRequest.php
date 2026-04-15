<?php

namespace App\Http\Requests\Intranet\Tracking;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TrackingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->has('tracking')) {
            $this->merge($this->tracking);
        }
    }

    public function rules(): array
    {
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'folio' => ['nullable'],
                'cliente_id' => ['nullable', 'exists:clientes,id'],
                'prospecto_id' => ['nullable', 'exists:tracking_prospectos,id'],
                'origen_track_id' => ['required', 'exists:tracking_origen,id'],
                'vendedor_id' => ['required', 'exists:empleados,id'],
                'sucursal_id' => ['required', 'exists:sucursales,id'],
                'depto_id' => ['required', 'exists:tracking_depto,id'],

                'category_id' => ['required', 'exists:categories,id'],
                'condicion_pago_id' => ['required', 'exists:products_condicion_pago,id'],
                'currency_id' => ['required', 'exists:currency,id'],
                'subtotal' => ['required', 'numeric'],
                'iva_monto' => ['required', 'numeric'],
                'incluye_iva' => ['required', 'boolean'],
                'tarifa_cambio' => ['required', 'numeric'],
                'descuento' => ['required', 'numeric'],
                'total' => ['required', 'numeric'],
                'factura' => ['nullable', 'string'],
                'date_lost_sale' => ['nullable', 'date'],
                'date_won_sale' => ['nullable', 'date'],
                'date_factura' => ['nullable', 'date'],
                'date_delivery' => ['nullable', 'date'],
                'notas' => ['nullable', 'string'],

                //detalles
                'detalles' => ['nullable', 'array'],
                'detalles.*.producto_id' => ['required', 'exists:products,id'],
                'detalles.*.cantidad' => ['required', 'numeric'],
                'detalles.*.precio_unidad' => ['required', 'numeric'],
                'detalles.*.subtotal' => ['required', 'numeric'],

                // extras
                'extras' => ['nullable', 'array'],
                'extras.*.extra_id' => ['required', 'exists:product_extras,id'],
                'extras.*.cantidad' => ['required', 'numeric'],
                'extras.*.precio_unidad' => ['required', 'numeric'],
                'extras.*.subtotal' => ['required', 'numeric'],

                'activity' => ['nullable', 'array'],
                'activity.certeza_id' => ['required_with:activity', 'exists:tracking_certeza,id'],
                'activity.tipo_seguimiento_id' => ['required_with:activity', 'exists:tracking_tipo_seguimiento,id'],
                'activity.currency_id' => ['required_with:activity', 'exists:currency,id'],
                'activity.tarifa_cambio' => ['required_with:activity', 'numeric'],
                'activity.ultimo_precio_tratar' => ['nullable', 'numeric'],
                'activity.notas' => ['nullable', 'string'],
                'activity.date_next_tracking' => ['nullable', 'date'],

            ];
        }
        return [
            'folio' => ['nullable'],
            'cliente_id' => ['nullable', 'exists:clientes,id'],
            'prospecto_id' => ['nullable', 'exists:tracking_prospectos,id'],
            'origen_track_id' => ['required', 'exists:tracking_origen,id'],
            'vendedor_id' => ['required', 'exists:empleados,id'],
            'sucursal_id' => ['required', 'exists:sucursales,id'],
            'depto_id' => ['required', 'exists:tracking_depto,id'],

            'category_id' => ['required', 'exists:categories,id'],
            'condicion_pago_id' => ['required', 'exists:products_condicion_pago,id'],
            'currency_id' => ['required', 'exists:currency,id'],
            'subtotal' => ['required', 'numeric'],
            'iva_monto' => ['required', 'numeric'],
            'incluye_iva' => ['required', 'boolean'],
            'tarifa_cambio' => ['required', 'numeric'],
            'descuento' => ['required', 'numeric'],
            'total' => ['required', 'numeric'],
            'factura' => ['nullable', 'string'],
            'date_lost_sale' => ['nullable', 'date'],
            'date_won_sale' => ['nullable', 'date'],
            'date_factura' => ['nullable', 'date'],
            'date_delivery' => ['nullable', 'date'],
            'notas' => ['nullable', 'string'],

            //detalles
            'detalles' => ['nullable', 'array'],
            'detalles.*.producto_id' => ['required', 'exists:products,id'],
            'detalles.*.cantidad' => ['required', 'numeric'],
            'detalles.*.precio_unidad' => ['required', 'numeric'],
            'detalles.*.subtotal' => ['required', 'numeric'],

            // extras
            'extras' => ['nullable', 'array'],
            'extras.*.extra_id' => ['required', 'exists:product_extras,id'],
            'extras.*.cantidad' => ['required', 'numeric'],
            'extras.*.precio_unidad' => ['required', 'numeric'],
            'extras.*.subtotal' => ['required', 'numeric'],

            'activity' => ['nullable', 'array'],
            'activity.certeza_id' => ['required_with:activity', 'exists:tracking_certeza,id'],
            'activity.tipo_seguimiento_id' => ['required_with:activity', 'exists:tracking_tipo_seguimiento,id'],
            'activity.currency_id' => ['required_with:activity', 'exists:currency,id'],
            'activity.tarifa_cambio' => ['required_with:activity', 'numeric'],
            'activity.ultimo_precio_tratar' => ['nullable', 'numeric'],
            'activity.notas' => ['nullable', 'string'],
            'activity.date_next_tracking' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
            'prospecto_id.exists' => 'El prospecto seleccionado no existe.',
            'origen_track_id.required' => 'El campo origen de seguimiento es obligatorio.',
            'origen_track_id.exists' => 'El origen de seguimiento seleccionado no existe.',
            'vendedor_id.required' => 'El campo vendedor es obligatorio.',
            'vendedor_id.exists' => 'El vendedor seleccionado no existe.',
            'sucursal_id.required' => 'El campo sucursal es obligatorio.',
            'sucursal_id.exists' => 'La sucursal seleccionada no existe.',
            'depto_id.required' => 'El campo departamento es obligatorio.',
            'depto_id.exists' => 'El departamento seleccionado no existe.',
            'category_id.required' => 'El campo categoría es obligatorio.',
            'category_id.exists' => 'La categoría seleccionada no existe.',
            'condicion_pago_id.required' => 'El campo condición de pago es obligatorio.',
            'condicion_pago_id.exists' => 'La condición de pago seleccionada no existe.',
            'currency_id.required' => 'El campo moneda es obligatorio.',
            'currency_id.exists' => 'La moneda seleccionada no existe.',
            'detalles.array' => 'El formato de detalles es inválido.',
            'detalles.*.producto_id.required' => 'El producto es obligatorio.',
            'detalles.*.producto_id.exists' => 'El producto no existe.',
            'detalles.*.cantidad.required' => 'La cantidad es obligatoria.',
            'detalles.*.cantidad.numeric' => 'La cantidad debe ser un número.',
            'detalles.*.precio_unidad.required' => 'El precio por unidad es obligatorio.',
            'detalles.*.precio_unidad.numeric' => 'El precio por unidad debe ser un número.',
            'detalles.*.subtotal.required' => 'El subtotal es obligatorio.',
            'detalles.*.subtotal.numeric' => 'El subtotal debe ser un número.',

            'activity.array' => 'El formato de actividad debe ser un array',
            'activity.certeza_id.required' => 'El campo actividad certeza es obligatorio.',
            'activity.certeza_id.exists' => 'La actividad certeza seleccionada no existe.',
            'activity.currency_id.required' => 'El campo moneda es obligatorio.',
            'activity.tipo_seguimiento_id' => 'El campo de tipo de seguimiento es obligatorio',
            'activity.currency_id.exists' => 'La moneda seleccionada no existe.',
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
