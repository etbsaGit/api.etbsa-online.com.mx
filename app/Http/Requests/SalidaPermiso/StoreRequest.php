<?php

namespace App\Http\Requests\SalidaPermiso;

use App\Models\SalidaPermiso;
use Illuminate\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

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
            'date' => ['required', 'date'],
            'start' => ['required', 'date_format:H:i'],
            'end' => ['required', 'date_format:H:i', 'after:start'],
            'lunch_start' => ['nullable', 'date_format:H:i'],
            'lunch_end' => ['nullable', 'date_format:H:i', 'after:lunch_start'],
            'status' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
            'feedback' => ['nullable', 'string', 'max:1000'],
            'empleado_id' => ['required', 'exists:empleados,id'],
            'sucursal_id' => ['required', 'exists:sucursales,id'],
        ];
    }

    /**
     * Mensajes personalizados.
     */
    public function messages(): array
    {
        return [
            'date.required' => 'La fecha es obligatoria.',
            'start.required' => 'La hora de inicio es obligatoria.',
            'end.after' => 'La hora de fin debe ser posterior a la de inicio.',
            'empleado_id.exists' => 'El empleado seleccionado no existe.',
        ];
    }

    /**
     * Validación adicional para evitar duplicados en el mismo día.
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $empleadoId = $this->input('empleado_id');
            $date = $this->input('date');

            if ($empleadoId && $date) {
                // Verifica si ya existe otro permiso del mismo empleado en la misma fecha
                $query = SalidaPermiso::where('empleado_id', $empleadoId)
                    ->where('date', $date);

                // Si es una actualización, excluir el registro actual
                if ($this->route('salidaPermiso')) {
                    $query->where('id', '!=', $this->route('salidaPermiso')->id);
                }

                if ($query->exists()) {
                    $validator->errors()->add('date', 'Este empleado ya tiene un permiso registrado en esta fecha.');
                }
            }
        });
    }
}
