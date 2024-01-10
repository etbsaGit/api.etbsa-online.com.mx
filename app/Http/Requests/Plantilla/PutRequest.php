<?php

namespace App\Http\Requests\Plantilla;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

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
            "nombre"=>['required','string','max:255',Rule::unique('plantillas')->ignore($this->route("plantilla")->id)],
            'requisito_id'=>['required','array']
        ];
    }
}
