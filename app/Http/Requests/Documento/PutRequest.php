<?php

namespace App\Http\Requests\Documento;

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
            "nombre" => ['required', 'string', 'max:255', Rule::unique('documentos')->ignore($this->route("documento")->id)],
            "fecha_de_vencimiento" => ['required', 'date'],
            "comentario"=>['nullable', 'string', 'max:255'],

            "requisito_id" => ['required', 'integer'],
            "expediente_id" => ['required', 'integer'],
            "estatus_id" => ['required', 'integer'],

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