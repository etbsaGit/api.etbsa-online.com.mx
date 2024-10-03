<?php

namespace App\Http\Requests\Intranet\Sale;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreSaleRequest extends FormRequest
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
            "amount" => ['nullable', 'numeric'],
            "comments" => ['nullable', 'string', 'max:255'],
            "serial" => ['nullable', 'string', 'max:255','unique:sales,serial'],
            "invoice" => ['nullable', 'string', 'max:255','unique:sales,invoice'],
            "order" => ['nullable', 'string', 'max:255','unique:sales,order'],
            "folio" => ['nullable', 'string', 'max:255','unique:sales,folio'],
            "economic" => ['nullable', 'string', 'max:255'],
            'validated' => ['nullable', 'boolean'],
            'date' => ['nullable', 'date'],
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'status_id' => ['required', 'integer', 'exists:estatus,id'],
            'referencia_id' => ['nullable', 'integer', 'exists:referencias,id'],
            'empleado_id' => ['required', 'integer', 'exists:empleados,id'],
            'sucursal_id' => ['required', 'integer', 'exists:sucursales,id'],
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
