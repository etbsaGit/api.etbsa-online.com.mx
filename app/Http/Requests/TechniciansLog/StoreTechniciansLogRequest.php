<?php

namespace App\Http\Requests\TechniciansLog;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreTechniciansLogRequest extends FormRequest
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
        // Obtén el usuario autenticado
        $user = Auth::user();

        // Verifica si el usuario tiene una relación `empleado` y si el `empleado` tiene una relación `puesto` con el nombre `tecnico`
        if ($user && $user->empleado) {
            $puesto = $user->empleado->puesto; // Obtén el puesto asociado al empleado

            if ($puesto && $puesto->name === 'tecnico') {
                $this->merge([
                    'tecnico_id' => $user->empleado->id,
                ]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'fecha' => ['required', 'date'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_termino' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'comentarios' => ['nullable', 'string'],
            'tecnico_id' => ['required', 'integer', 'exists:empleados,id'],
            'wo_id' => ['nullable', 'integer', 'exists:work_orders,id'],
            'activity_technician_id' => ['required', 'integer', 'exists:activity_technicians,id'],
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
