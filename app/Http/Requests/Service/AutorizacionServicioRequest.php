<?php

namespace App\Http\Requests\Service;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AutorizacionServicioRequest extends FormRequest
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
            'orden_servicio_id' => ['required', 'exists:vehicles_ordenes_servicio,id'],
            'autorizado' => ['required', 'boolean'],
            'comentarios' => [
                Rule::requiredIf(function () {
                    return filter_var($this->input('autorizado'), FILTER_VALIDATE_BOOLEAN) === false;
                }),
                'nullable',
                'string',
            ],
        ];
    }

    public function messages()
    {
        return [
            'orden_servicio_id.required' => 'La orden de servicio es obligatoria',
            'orden_servicio_id.exists' => 'La orden de servicio no existe',
            'autorizado.required' => 'La autorización es obligatoria',
            'comentarios.required' => 'El comentario es obligatorio cuando no se autoriza la orden',
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
