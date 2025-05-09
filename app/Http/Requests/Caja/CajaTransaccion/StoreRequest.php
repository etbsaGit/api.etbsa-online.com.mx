<?php

namespace App\Http\Requests\Caja\CajaTransaccion;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'factura' => ['required', 'string', 'unique:caja_transacciones'],
            'folio' => ['required', 'string', 'unique:caja_transacciones'],
            'serie' => ['required', 'string', 'unique:caja_transacciones'],
            'uuid' => ['required', 'string', 'unique:caja_transacciones'],
            'comentarios' => ['nullable', 'string'],
            "validado" => ['required', 'boolean'],
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'user_id' => ['required', 'exists:users,id'],
            'tipo_factura_id' => ['required', 'integer', 'exists:caja_tipos_facturas,id'],
            'fecha_pago' => ['required', 'date'],
            'cuenta_id' => ['required', 'integer', 'exists:caja_cuentas,id'],
            'tipo_pago_id' => ['required', 'integer', 'exists:caja_tipos_pagos,id'],

            // Validación del array pagos
            'pagos' => ['required', 'array', 'min:1'],
            'pagos.*.monto' => ['required', 'numeric'],
            'pagos.*.descripcion' => ['required', 'string'],

            'pagos.*.sucursal_id' => ['required', 'integer', 'exists:sucursales,id'],
            'pagos.*.categoria_id' => ['required', 'integer', 'exists:caja_categorias,id'],

        ];
    }

    function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            $response = new Response($validator->errors(), 422);
            throw new ValidationException($validator, $response);
        }
    }
}
