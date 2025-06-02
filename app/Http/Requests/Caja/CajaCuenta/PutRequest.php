<?php

namespace App\Http\Requests\Caja\CajaCuenta;

use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class PutRequest extends FormRequest
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
            'numeroCuenta' => ['required', 'string', Rule::unique('caja_cuentas')->ignore($this->route('cajaCuentum')->id)],
            'descripcion' => ['nullable', 'string'],
            'moneda' => ['required', 'string'],
            'caja_banco_id' => ['required', 'integer', 'exists:caja_bancos,id'],
            'sucursal_id' => ['required', 'integer', 'exists:sucursales,id'],
            'caja_categoria_id' => ['required', 'integer', 'exists:caja_categorias,id'],
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
