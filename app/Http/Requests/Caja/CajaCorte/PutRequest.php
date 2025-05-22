<?php

namespace App\Http\Requests\Caja\CajaCorte;

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
            'efectivo' => ['required', 'numeric'],
            'tarjeta_debito' => ['required', 'numeric'],
            'tarjeta_credito' => ['required', 'numeric'],
            'transferencias' => ['required', 'numeric'],
            'depositos' => ['required', 'numeric'],
            'cheques' => ['required', 'numeric'],

            'fecha_corte' => ['required', 'date', Rule::unique('caja_cortes')->ignore($this->route('cajaCorte')->id)],

            'descripcion' => ['nullable', 'string'],

            'user_id' => ['required', 'integer', 'exists:users,id'],
            'sucursal_id' => ['required', 'integer', 'exists:sucursales,id'],
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
