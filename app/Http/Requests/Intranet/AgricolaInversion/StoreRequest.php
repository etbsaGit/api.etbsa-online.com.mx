<?php

namespace App\Http\Requests\Intranet\AgricolaInversion;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;

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
            'hectareas' => ['required', 'numeric'],
            'costo' => ['required', 'integer'],
            'toneladas' => ['required', 'integer'],
            'precio' => ['required', 'integer'],
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'cultivo_id' => ['required', 'integer', 'exists:cultivos,id'],
        ];
    }

    public function messages(): array{
        return[
            'year.required' => 'Selecciona el año',
            'ciclo.required' => 'Selecciona el ciclo',
            'hectareas.required' => 'Se deben indicar las hectareas',
            'costo.required' => 'Se debe indicar el costo',
            'toneladas.required' => 'Se deben indicar las toneladas',
            'precio.required' => 'Se debe indicar el precio',
            'cultivo_id.required' => 'Selecciona el cultivo'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Errores de validación',
            'errors'  => $validator->errors()
        ], 422));
    }
}
