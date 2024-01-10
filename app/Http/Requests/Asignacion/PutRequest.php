<?php

namespace App\Http\Requests\Asignacion;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class PutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "nombre" => ['required', 'string', 'max:255',Rule::unique('asignaciones')->ignore($this->route("Asignacion")->id)],
            "descripcion" => ['required', 'string', 'max:255','unique:asignaciones,descripcion'],
            "tipo_de_asignacion_id"=>['required','integer'],
            "empleado_id"=>['required','integer'],

        ];
    }
}
