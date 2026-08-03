<?php

namespace App\Http\Requests\Intranet\ClientesDoc;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreClientesDocRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'extension' => ['required', 'string', 'max:255'],
            'expiration_date' => ['nullable', 'date'],
            'comments' => ['nullable', 'string', 'max:255'],
            'base64' => ['required', 'string'],
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'status_id' => ['required', 'integer', 'exists:estatus,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Ingrese el nombre del documento.',
            'extension.required' => 'No se pudo identificar el tipo de archivo.',
            'base64.required' => 'Seleccione un archivo para continuar.',
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
