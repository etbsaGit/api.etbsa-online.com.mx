<?php

namespace App\Http\Requests\Estudio;

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
            "inicio"=>['required','date'],
            "termino"=>['nullable','date'],
            "documentoQueAvala_id"=>['required', 'integer'],
            "estadoDelEstudio_id"=>['required', 'integer'],
            "escolaridad_id"=>['required', 'integer'],
            "empleado_id"=>['required','integer'],
            "escuela_id"=>['required','integer'],


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
