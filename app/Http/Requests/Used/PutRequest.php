<?php

namespace App\Http\Requests\Used;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

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
            "name" => ['required', 'string', 'max:191'],
            "description" => ['required', 'string'],
            "comments" => ['nullable', 'string'],
            "serial" => ['required', 'string', 'max:191', Rule::unique('useds')->ignore($this->route("used")->id)],
            "status" => ['nullable', 'boolean'],
            "year" => ['required', 'string', 'max:4'],
            "hours" => ['nullable', 'numeric'],
            "cost" => ['nullable', 'numeric'],
            "price" => ['required', 'numeric'],
            "origin_id" => ['required', 'integer', 'exists:sucursales,id'],
            "location_id" => ['required', 'integer', 'exists:sucursales,id'],
            "tipo_equipo_id" => ['required', 'integer', 'exists:tipos_equipo,id'],
            "linea_id" => ['required', 'integer', 'exists:lineas,id'],
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
