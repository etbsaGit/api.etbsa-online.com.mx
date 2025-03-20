<?php

namespace App\Http\Requests\Suggestion;

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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "title" => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'boolean'],
            'application' => ['nullable', 'boolean'],
            "description" => ['nullable', 'string', 'max:255'],
            "feedback" => ['nullable', 'string', 'max:255'],
            'estatus_id' => ['required', 'integer', 'exists:estatus,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
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
