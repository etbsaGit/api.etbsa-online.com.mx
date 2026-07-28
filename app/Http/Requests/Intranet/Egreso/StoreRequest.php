<?php

namespace App\Http\Requests\Intranet\Egreso;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
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
            'pago' => ['required', 'numeric', 'min:0'],
            'months' => ['required', 'integer', 'min:1'],
            'type' => ['required', 'integer'],
            'entidad' => ['required', 'string', 'max:255'],
            'concepto' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'year.required' => 'Selecciona el año',
            'pago.required' => 'Se debe especificar el pago',
            'months.required' => 'Se deben especificar los pagos restantes',
            'type.required' => 'Se debe especificar el tipo de pago',
            'entidad.required' => 'Se debe especificar la entidad a quien se debe',
            'concepto.required' => 'se debe especificar el detalle'
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
