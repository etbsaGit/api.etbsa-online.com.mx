<?php

namespace App\Http\Requests\Intranet\Ganado;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GanadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $item = $this->route('ganado');
        return [
            'name' => ['required', 'string', 'max:191', Rule::unique('ganados', 'name')->ignore($item?->id)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del ganado es obligatorio',
            'name.unique'   => 'Ya existe un ganado con este nombre',
            'name.max'      => 'El nombre no puede exceder 191 caracteres',
        ];
    }

    /**
     * 🚨 CLAVE: mismo formato del ApiController
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Errores de validación',
            'errors'  => $validator->errors()
        ], 422));
    }
}
