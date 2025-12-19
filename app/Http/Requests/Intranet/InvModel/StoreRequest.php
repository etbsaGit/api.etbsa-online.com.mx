<?php

namespace App\Http\Requests\Intranet\InvModel;

use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

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
        // Modelo en la ruta (null en store, objeto en update)
        $item = $this->route('invModel'); // cámbialo al nombre real del parámetro de ruta
        return [
            'code' => ['required', 'string', 'max:255', Rule::unique('inv_models', 'code')->ignore($item?->id),],
            'name' => ['required', 'string', 'max:255', Rule::unique('inv_models', 'name')->ignore($item?->id),],
            'description' => ['nullable', 'string',],
            'price' => ['nullable', 'numeric', 'min:0',],
            'inv_configurations' => ['present', 'array'],
            'inv_configurations.*' => ['integer', 'exists:inv_configurations,id'],
            'base64' => ['nullable', 'string'],
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
