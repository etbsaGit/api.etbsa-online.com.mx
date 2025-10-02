<?php

namespace App\Http\Requests\Intranet\Analitica;

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
            "titulo" => ['required', 'string', 'max:191'],
            'efectivo' => ['required', 'numeric', 'min:0'],
            'caja' => ['required', 'numeric', 'min:0'],
            'gastos' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'boolean'],
            'fecha' => ['required', 'date'],
            "comentarios" => ['nullable', 'string', 'max:191'],
            'cliente_id' => ['required', 'exists:clientes,id'],
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
