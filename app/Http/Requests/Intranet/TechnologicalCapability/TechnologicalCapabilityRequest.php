<?php

namespace App\Http\Requests\Intranet\TechnologicalCapability;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TechnologicalCapabilityRequest extends FormRequest
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
        $item = $this->route('technologicalCapability');

        return [
            'name' => ['required', 'string', 'max:191', Rule::unique('technological_capabilities', 'name')->ignore($item?->id)],
            'level' => ['required', 'string', 'max:191'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.string'   => 'El nombre debe ser texto válido.',
            'name.max'      => 'El nombre no puede exceder los 191 caracteres.',
            'name.unique'   => 'Ya existe una capacidad tecnológica con ese nombre.',

            'level.required' => 'El nivel es obligatorio.',
            'level.string'   => 'El nivel debe ser texto válido.',
            'level.max'      => 'El nivel no puede exceder los 191 caracteres.',
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
