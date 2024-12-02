<?php

namespace App\Http\Requests\RentalMachine;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreRentalMachineRequest extends FormRequest
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
            'base64' => ['nullable', 'string'],
            'serial' => ['required','string','max:255','unique:rental_machines'],
            'model' => ['required','string','max:255'],
            'description' => ['nullable','string','max:1000'],
            'hours' => ['required','integer','min:0'],
            'comments' => ['nullable','string','max:1000'],
            'status' => ['required','in:available,rented,maintenance'],
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
