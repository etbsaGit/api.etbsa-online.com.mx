<?php

namespace App\Http\Requests\Intranet\InversionesGanadera;

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
            'unidades' => ['required', 'integer'],
            'costo' => ['required', 'integer'],
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'ganado_id' => ['required', 'integer', 'exists:ganados,id'],
        ];
    }
    public function messages():array
    {
        return[
            'year.required' => 'Selecciona el año',
            'ciclo.required' => 'Selecciona el ciclo',
            'unidades.required' => 'Se deben indicar las unidades',
            'costo.required' => 'Se debe indicar el costo',
            'ganado_id.required' => 'Selecciona el ganado'
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
