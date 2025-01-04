<?php

namespace App\Http\Requests\VacationDay;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class VacationDayRequest extends FormRequest
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
            'vehiculo_utilitario' => ['nullable', 'string', 'max:255'],
            'periodo_correspondiente' => ['required', 'string', 'max:255'],
            'anios_cumplidos' => ['required', 'integer'],
            'dias_periodo' => ['required', 'integer'],
            'subtotal_dias' => ['required', 'integer'],
            'dias_disfrute' => ['required', 'integer'],
            'dias_pendientes' => ['required', 'integer'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_termino' => ['required', 'date'],
            'fecha_regreso' => ['required', 'date'],
            'validated' => ['nullable', 'boolean'],
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
