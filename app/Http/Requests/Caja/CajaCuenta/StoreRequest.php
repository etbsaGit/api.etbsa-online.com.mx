<?php

namespace App\Http\Requests\Caja\CajaCuenta;

use Illuminate\Http\Response;
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'numeroCuenta' => ['required', 'string', 'unique:caja_cuentas'],
            'descripcion' => ['nullable', 'string'],
            'moneda' => ['required', 'string'],
            'numero_banco' => ['required', 'integer'],
            'caja_banco_id' => ['required', 'integer', 'exists:caja_bancos,id'],
            'sucursal_id' => ['required', 'integer', 'exists:sucursales,id'],
            'caja_categoria_id' => ['nullable', 'integer', 'exists:caja_categorias,id'],
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
