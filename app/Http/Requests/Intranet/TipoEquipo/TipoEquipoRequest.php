<?php

namespace App\Http\Requests\Intranet\TipoEquipo;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TipoEquipoRequest extends FormRequest
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
        $item = $this->route('tipoEquipo');
        return [
            'name' => ['required', 'string', 'max:191', Rule::unique('tipos_equipo', 'name')->ignore($item?->id)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del tipo de maquina es obligatorio',
            'name.unique'   => 'Ya existe un tipo de maquina con este nombre',
            'name.max'      => 'El nombre no puede exceder 191 caracteres',
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
