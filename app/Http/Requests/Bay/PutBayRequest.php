<?php

namespace App\Http\Requests\Bay;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;


class PutBayRequest extends FormRequest
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
            'nombre' => 'required|string|max:255',
            'cliente' => 'nullable|string|max:255',
            'maquina' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'tecnico_id' => 'nullable|exists:empleados,id',
            'sucursal_id' => 'required|exists:sucursales,id',
            'linea_id' => 'required|exists:lineas,id',
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
