<?php

namespace App\Http\Requests\Visit;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

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
            'dia' => ['required', 'date'],
            'ubicacion' => ['required', 'string', 'max:255'],
            'comentarios' => ['nullable', 'string', 'max:65000'],
            'retroalimentacion' => ['nullable', 'string', 'max:65000'],
            'prospect_id' => ['required', 'integer', 'exists:prospects,id'],
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
