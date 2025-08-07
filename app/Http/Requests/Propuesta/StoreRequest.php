<?php

namespace App\Http\Requests\Propuesta;

use Illuminate\Http\Response;
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

    protected function prepareForValidation()
    {
        $user = auth()->user();

        $this->merge([
            'created_by' => $user->id,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'nullable|boolean',
            'notas' => 'nullable|string',
            "base64" => 'nullable|string',
            'url' => 'nullable|url|max:255',
            'start_date' => 'nullable|date|before_or_equal:end_date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'inversion' => 'nullable|numeric|min:0',
            'estatus_id' => 'required|exists:estatus,id',
            'linea_id' => 'nullable|exists:lineas,id',
            'departamento_id' => 'required|exists:departamentos,id',
            'created_by' => 'required|exists:users,id',
            'auth_by' => 'nullable|exists:users,id',
            'auth_at' => 'nullable|date',
            'category_id' => 'nullable|exists:categories,id',
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
