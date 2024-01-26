<?php

namespace App\Http\Requests\Desvinculacion;

use Illuminate\Foundation\Http\FormRequest;

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
            "fecha" => ['required', 'date'],
            "comentarios" => ['required', 'string', 'max:255'],
            "tipo_de_desvinculacion_id"=>['required','integer'],
        ];
    }
}
