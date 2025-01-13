<?php

namespace App\Http\Requests\Incapacity;

use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class IncapacityPutRequest extends FormRequest
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
            'folio' => ['required',  Rule::unique('incapacities')->ignore($this->route('incapacity')->id)],
            'inicial' => ['required', 'boolean'],
            'empleado_id' => ['required', 'integer', 'exists:empleados,id'],
            'sucursal_id' => ['required', 'integer', 'exists:sucursales,id'],
            'puesto_id' => ['required', 'integer', 'exists:puestos,id'],
            'estatus_id' => ['required', 'integer', 'exists:estatus,id'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_termino' => ['required', 'date'],
            'fecha_regreso' => ['required', 'date'],
            'comentarios' => ['nullable', 'string', 'max:255'],
            'incapacity_id' => ['nullable', 'integer', 'exists:incapacities,id'],

            'children' => ['nullable', 'array'], // Valida que sea un arreglo
            'children.*.id' => ['required'], // Valida que cada `id` sea un UUID
            'children.*.folio' => ['required', 'string'],
            'children.*.inicial' => ['required', 'boolean'],
            'children.*.empleado_id' => ['required', 'integer', 'exists:empleados,id'],
            'children.*.sucursal_id' => ['required', 'integer', 'exists:sucursales,id'],
            'children.*.puesto_id' => ['required', 'integer', 'exists:puestos,id'],
            'children.*.estatus_id' => ['required', 'integer', 'exists:estatus,id'],
            'children.*.fecha_inicio' => ['required', 'date'],
            'children.*.fecha_termino' => ['required', 'date'],
            'children.*.fecha_regreso' => ['required', 'date'],
            'children.*.comentarios' => ['nullable', 'string', 'max:255'],
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
