<?php

namespace App\Http\Requests\Caja\CajaCorte;

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
        $user = Auth::user();

        $this->merge([
            'user_id' => $user->id,
            'sucursal_id' => optional($user->empleado)->sucursal_id,
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
            'efectivo' => ['required', 'numeric'],
            'tarjeta_debito' => ['required', 'numeric'],
            'tarjeta_credito' => ['required', 'numeric'],
            'transferencias' => ['required', 'numeric'],
            'depositos' => ['required', 'numeric'],
            'cheques' => ['required', 'numeric'],

            'fecha_corte' => ['required', 'date', 'unique:caja_cortes'],

            'descripcion' => ['nullable', 'string'],

            'user_id' => ['required', 'integer', 'exists:users,id'],
            'sucursal_id' => ['required', 'integer', 'exists:sucursales,id'],

            'detalleEfectivo' => ['required', 'array'],
            'detalleEfectivo.*.cantidad' => ['required', 'integer', 'min:0'],
            'detalleEfectivo.*.denominacion_id' => ['required', 'integer', 'exists:caja_denominaciones,id'],
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
