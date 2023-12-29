<?php

namespace App\Http\Requests\Sucursal;

use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class PutRequest extends FormRequest
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
            "nombre" => ['required', 'string', 'max:255', Rule::unique('sucursales')->ignore($this->route('sucursal')->id)],
            "direccion" => ['required', 'string', 'max:255', Rule::unique('sucursales')->ignore($this->route('sucursal')->id)],
            "encargado_id" => ['nullable', 'integer', Rule::unique('sucursales')->ignore($this->route('sucursal')->id)]
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
