<?php

namespace App\Http\Requests\Intranet\CreditoInterno;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class VoBoCreditoRequest extends FormRequest
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
            'solicitud_id' => ['required', 'exists:credito_solicitud,id'],
            'aprobado' => ['required', 'boolean'],
            'notas' => ['nullable', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'solicitud_id.required' => 'La solicitud es obligatoria',
            'solicitud_id.exists' => 'La solicitud no existe',
            'aprobado.required' => 'El aprobacion es obligatorio',
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
