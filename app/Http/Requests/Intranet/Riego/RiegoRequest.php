<?php

namespace App\Http\Requests\Intranet\Riego;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RiegoRequest extends FormRequest
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
        $item = $this->route('riego');
        return [
            'name' => ['required', 'string', 'max:191', Rule::unique('riegos', 'name')->ignore($item?->id)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del riego es obligatorio',
            'name.unique'   => 'Ya existe un riego con este nombre',
            'name.max'      => 'El nombre no puede exceder 191 caracteres',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->respond([
            'success' => false,
            'message' => 'Errores de validación',
            'errors'  => $validator->errors()
        ], 422));
    }
}
