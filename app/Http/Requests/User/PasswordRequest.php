<?php

namespace App\Http\Requests\User;

use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class PasswordRequest extends FormRequest
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
            "old_password"=>['required','string','max:255'],
            "password" => ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'],
        ];
    }

    public function messages()
    {
        return [
            'old_password.required' => 'El campo de la contraseña anterior es obligatorio.',
            'password.required' => 'El campo de la nueva contraseña es obligatorio.',
            'password.min' => 'La nueva contraseña debe tener al menos :min caracteres.',
            'password.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
            'password.regex' => 'La nueva contraseña debe contener al menos una letra y un número.',
        ];
    }

    function failedValidation(Validator $validator)
    {
        if($this->expectsJson())
            {
                $response = new Response($validator->errors(),422);
                throw new ValidationException($validator, $response);
            }
    }
}
