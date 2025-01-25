<?php

namespace App\Http\Requests\Visit;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreRequest extends FormRequest
{
        /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'cultivos' => is_array($this->cultivos) ? implode(', ', $this->cultivos) : $this->cultivos,
        ]);
    }
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
            'dia' => ['required', 'date'],
            'cliente' => ['required', 'string', 'max:255'],
            'ubicacion' => ['required', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:255'],
            'cultivos' => ['nullable'],
            'hectareas' => ['nullable', 'string', 'max:255'],
            'maquinaria' => ['nullable', 'string', 'max:255'],
            'comentarios' => ['nullable', 'string', 'max:255'],
            'retroalimentacion' => ['nullable', 'string', 'max:255'],
            'empleado_id' => ['required', 'integer', 'exists:empleados,id'],
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
