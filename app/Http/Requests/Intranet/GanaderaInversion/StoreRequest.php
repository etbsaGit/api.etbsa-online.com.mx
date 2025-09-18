<?php

namespace App\Http\Requests\Intranet\GanaderaInversion;

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
            'unidades' => ['required', 'integer'],
            'costo' => ['required', 'integer'],
            'cantidad' => ['required', 'integer'],
            'precio' => ['required', 'integer'],
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'ganado_id' => ['required', 'integer', 'exists:ganados,id'],
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
