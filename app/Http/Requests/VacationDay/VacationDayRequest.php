<?php

namespace App\Http\Requests\VacationDay;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
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
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'user_id' => auth()->id(),
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
            'empleado_id' => ['required', 'integer', 'exists:empleados,id'],
            'sucursal_id' => ['required', 'integer', 'exists:sucursales,id'],
            'puesto_id' => ['required', 'integer', 'exists:puestos,id'],
            'departamento_id' => ['required', 'integer', 'exists:departamentos,id'],
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
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'cubre' => ['required', 'integer', 'exists:empleados,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'empleado_id.required' => 'El empleado es obligatorio',
            'sucursal_id.required' => 'La sucursal es obligatoria',
            'puesto_id.required' => 'El puesto es obligatorio',
            'departamento_id.required' => 'El departamento es obligatorio',
            'periodo_correspondiente.required' => 'Especifica el periodo',
            'anios_cumplidos.required' => 'Los años cumplidos es obligatorio',
            'fecha_inicio.required' => 'Especifica la fecha de inicio',
            'fecha_termino.required' => 'Especifica la fecha de termino',
            'fecha_regreso.required' => 'Especifica la fecha de regreso',
            'cubre.required' => 'Selecciona quien te cubrirá',
            'dias_disfrute.required' => '',
            'dias_pendientes.required' =>''
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
