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

    public function rules(): array
    {
        return [
            'title' => ['required'],
            'folio' => ['nullable'],
            'cliente_id' => ['required', 'exists:clientes,id'],
            'origen_track_id' => ['required', 'exists:tracking_origen,id'],
            'vendedor_id' => ['required', 'exists:empleados,id'],
            'sucursal_id' => ['required', 'exists:sucursales,id'],
            'depto_id' => ['required', 'exists:tracking_depto,id'],
            'estatus_id' => ['required', 'exists:estatus,id'],
            'certeza_id' => ['required', 'exists:tracking_certeza,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'condicion_pago_id' => ['required', 'exists:products_condicion_pago,id'],
            'currency_id' => ['required', 'exists:currency,id'],
            'subtotal' => ['required', 'numeric'],
            'iva' => ['required', 'numeric'],
            'tarifa_cambio' => ['required', 'numeric'],
            'descuento' => ['required', 'numeric'],
            'total' => ['required', 'numeric'],
            'factura' => ['required', 'string'],
            'date_lost_sale' => ['required', 'date'],
            'date_won_sale' => ['required', 'date'],
            'date_factura' => ['required', 'date'],
            'date_delivery' => ['required', 'date'],

            //details
            'details' => ['nullable', 'array'],
            'details.*.product_id' => ['required', 'exists:products,id'],
            'details.*.cantidad' => ['required', 'numeric'],
            'details.*.precio_unidad' => ['required', 'numeric'],
            'details.*.subtotal' => ['required', 'numeric'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El campo título es obligatorio.',
            'cliente_id.required' => 'El campo cliente es obligatorio.',
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
            'origen_track_id.required' => 'El campo origen de seguimiento es obligatorio.',
            'origen_track_id.exists' => 'El origen de seguimiento seleccionado no existe.',
            'vendedor_id.required' => 'El campo vendedor es obligatorio.',
            'vendedor_id.exists' => 'El vendedor seleccionado no existe.',
            'sucursal_id.required' => 'El campo sucursal es obligatorio.',
            'sucursal_id.exists' => 'La sucursal seleccionada no existe.',
            'depto_id.required' => 'El campo departamento es obligatorio.',
            'depto_id.exists' => 'El departamento seleccionado no existe.',
            'estatus_id.required' => 'El campo estatus es obligatorio.',
            'estatus_id.exists' => 'El estatus seleccionado no existe.',
            'certeza_id.required' => 'El campo certeza es obligatorio.',
            'certeza_id.exists' => 'La certeza seleccionada no existe.',
            'category_id.required' => 'El campo categoría es obligatorio.',
            'category_id.exists' => 'La categoría seleccionada no existe.',
            'condicion_pago_id.required' => 'El campo condición de pago es obligatorio.',
            'condicion_pago_id.exists' => 'La condición de pago seleccionada no existe.',
            'currency_id.required' => 'El campo moneda es obligatorio.',
            'currency_id.exists' => 'La moneda seleccionada no existe.',
            'detalles.array' => 'El formato de detalles es inválido.',
            'detalles.*.product_id.required' => 'El producto es obligatorio.',
            'detalles.*.product_id.exists' => 'El producto no existe.',
            'detalles.*.cantidad.required' => 'La cantidad es obligatoria.',
            'detalles.*.cantidad.numeric' => 'La cantidad debe ser un número.',
            'detalles.*.precio_unidad.required' => 'El precio por unidad es obligatorio.',
            'detalles.*.precio_unidad.numeric' => 'El precio por unidad debe ser un número.',
            'detalles.*.subtotal.required' => 'El subtotal es obligatorio.',
            'detalles.*.subtotal.numeric' => 'El subtotal debe ser un número.',
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
