<?php

namespace App\Http\Requests\User;

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
            "name"=>['required','string','max:255'],
            "email"=>['required','email',Rule::unique('users')->ignore($this->route("user")->id)],
            "password"=>['nullable','string','max:255'],
            'roles' => ['nullable','array'],
            'roles.*' => ['nullable','string','exists:roles,name'],
            'permissions' => ['nullable','array'],
            'permissions.*' => ['nullable','string','exists:permissions,name'],
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
