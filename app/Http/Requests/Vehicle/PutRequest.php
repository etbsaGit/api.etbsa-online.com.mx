<?php

namespace App\Http\Requests\Vehicle;

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
            'placas' => ['required', 'string', Rule::unique('vehicles')->ignore($this->route('vehicle')->id)],
            'departamento_id' => ['required', 'integer', 'exists:departamentos,id'],
            'linea_id' => ['required', 'integer', 'exists:lineas,id'],
            'sucursal_id' => ['required', 'integer', 'exists:sucursales,id'],
            'estatus_id' => ['required', 'integer', 'exists:estatus,id'],
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
