<?php

namespace App\Http\Requests\Intranet\InversionesAgricola;

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
            'year' => ['required', 'integer'],
            'ciclo' => ['required', 'string'],
            'hectareas' => ['required', 'integer'],
            'costo' => ['required', 'integer'],
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'cultivo_id' => ['required', 'integer', 'exists:cultivos,id'],
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
