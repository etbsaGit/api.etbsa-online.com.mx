<?php

namespace App\Http\Requests\ReferenciaPersonal;

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
        return [
            "nombre"=>['required','string','max:255','unique:referencias_personales,nombre'],
            "telefono"=>['required','numeric','digits:10','unique:referencias_personales,telefono'],
            "parentesco"=>['required','string','max:255'],
            "direccion"=>['required','string','max:255','unique:referencias_personales,direccion'],
            "empleado_id"=>['required','integer'],

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
