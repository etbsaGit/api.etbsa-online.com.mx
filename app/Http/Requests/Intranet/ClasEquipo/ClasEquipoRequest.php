<?php

namespace App\Http\Requests\Intranet\ClasEquipo;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ClasEquipoRequest extends FormRequest
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
        $item = $this->route('clasEquipo');
        return [
            'name' => ['required', 'string', 'max:191', Rule::unique('clas_equipos', 'name')->ignore($item?->id)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la clasificacion es obligatorio',
            'name.unique'   => 'Ya existe una clasificacion con este nombre',
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
