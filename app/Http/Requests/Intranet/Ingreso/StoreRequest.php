<?php

namespace App\Http\Requests\Intranet\Ingreso;

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
            "tipo" => ['required', 'string', 'max:191'],
            'monto' => ['required', 'numeric', 'min:1'],
            'costos' => ['required', 'numeric', 'min:0'],
            'cliente_id' => ['required', 'exists:clientes,id'],
            'year' => ['required', 'integer', 'digits:4', 'min:1900', 'max:' . date('Y')],
            'months' => ['required', 'integer', 'between:1,12'],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo.required' => 'Selecciona el tipo de ingreso',
            'monto.required' => 'Especifica el monto',
            'costos.required' => 'Especifica los costos',
            'year.required' => 'Selecciona el año',
            'months.required' => 'Selecciona los meses efectivos',
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
