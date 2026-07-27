<?php

namespace App\Http\Requests\Intranet\ClienteTechnology;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreClienteTechnologyRequest extends FormRequest
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
            "cantidad" => ['required', 'numeric'],
            "hectareas" => ['required', 'numeric'],
            'nueva_tecnologia_id' => ['required', 'integer', 'exists:nuevas_tecnologias,id'],
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
        ];
    }

    public function messages()
    {
        return [
        'cantidad.required' => 'Se debe especificar la cantidad',
        'hectareas.required' => 'Se debe especificar las hectáreas conectadas',
        'nueva_tecnologia_id' => 'Selecciona el tipo de tecnología'
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
