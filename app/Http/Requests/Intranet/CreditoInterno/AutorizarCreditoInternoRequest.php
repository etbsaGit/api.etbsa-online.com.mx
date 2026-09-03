<?php

namespace App\Http\Requests\Intranet\CreditoInterno;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AutorizarCreditoInternoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        return [
            'monto_aprobado' => ['nullable', 'numeric'],
        ];
    }

    public function messages()
    {
        return [
            'monto_aprobado.required' => 'El monto aprobado es obligatorio',
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
