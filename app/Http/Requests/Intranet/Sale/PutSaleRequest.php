<?php

namespace App\Http\Requests\Intranet\Sale;

use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class PutSaleRequest extends FormRequest
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
            "amount" => ['nullable', 'numeric'],
            "comments" => ['nullable', 'string', 'max:255'],
            "serial" => ['nullable', 'string', 'max:255',Rule::unique('sales')->ignore($this->route("sale")->id)],
            "invoice" => ['nullable', 'string', 'max:255',Rule::unique('sales')->ignore($this->route("sale")->id)],
            "order" => ['nullable', 'string', 'max:255',Rule::unique('sales')->ignore($this->route("sale")->id)],
            "folio" => ['nullable', 'string', 'max:255',Rule::unique('sales')->ignore($this->route("sale")->id)],
            "economic" => ['nullable', 'string'],
            'validated' => ['nullable', 'boolean'],
            "feedback" => ['nullable', 'string', 'max:255'],
            'date' => ['nullable', 'date'],
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'status_id' => ['required', 'integer', 'exists:estatus,id'],
            'referencia_id' => ['nullable', 'integer', 'exists:referencias,id'],
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
