<?php

namespace App\Http\Requests\Incapacity;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class IncapacityRequest extends FormRequest
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
            'empleado_id' => ['required', 'integer', 'exists:empleados,id'],
            'sucursal_id' => ['required', 'integer', 'exists:sucursales,id'],
            'puesto_id' => ['required', 'integer', 'exists:puestos,id'],
            'estatus_id' => ['required', 'integer', 'exists:estatus,id'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_termino' => ['required', 'date'],
            'fecha_regreso' => ['required', 'date'],
            'comentarios' => ['nullable', 'string', 'max:255'],
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
