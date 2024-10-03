<?php

namespace App\Http\Requests\Intranet\Sale;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreValidatedSaleRequest extends FormRequest
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
            '*.id' => 'required|integer',
            '*.feedback' => 'required|string',
            '*.validated' => 'required|boolean',
        ];
    }

    public function messages()
    {
        return [
            '*.id.required' => 'El campo id es obligatorio',
            '*.feedback.required' => 'La retroalimentacion es obligatoria',
            '*.validated.boolean' => 'Selecciona si es valido o no es valido',
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
