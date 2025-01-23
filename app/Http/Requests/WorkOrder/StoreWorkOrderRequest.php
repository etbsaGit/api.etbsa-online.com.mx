<?php

namespace App\Http\Requests\WorkOrder;

use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreWorkOrderRequest extends FormRequest
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
            'ot' => ['required', 'integer', 'unique:work_orders'],
            'cliente' => ['required', 'string', 'max:255'],
            'maquina' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string'],
            'fecha_ingreso' => ['required', 'date'],
            'fecha_entrega' => ['nullable', 'date'],
            'mano_obra' => ['nullable', 'numeric'],
            'refacciones' => ['nullable', 'numeric'],
            'horas_facturadas' => ['nullable', 'numeric'],
            'km' => ['nullable', 'numeric'],
            'foraneo' => ['nullable', 'numeric'],
            'horas_reales' => ['nullable', 'numeric'],
            'comentarios' => ['nullable', 'string', 'max:191'],
            'tecnico_id' => ['nullable', 'exists:empleados,id'],
            'estatus_id' => ['nullable', 'exists:estatus,id'],
            'estatus_taller_id' => ['nullable', 'exists:estatus,id'],
            'type_id' => ['nullable', 'exists:estatus,id'],
            'bay_id' => ['nullable', 'exists:bays,id', 'unique:work_orders,bay_id'],
            'sucursal_id' => ['nullable', 'exists:sucursales,id'],
            'linea_id' => ['nullable', 'exists:lineas,id'],
        ];
    }

    public function messages()
    {
        return [
            'bay_id.unique' => 'Ya existe una orden de trabajo asociada a esta bahia.',
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
