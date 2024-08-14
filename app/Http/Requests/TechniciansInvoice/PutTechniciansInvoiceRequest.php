<?php

namespace App\Http\Requests\TechniciansInvoice;

use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class PutTechniciansInvoiceRequest extends FormRequest
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
            "folio" => [
                'required',
                'string',
                'max:255',
                Rule::unique('technicians_invoices')->ignore($this->route('techniciansInvoice')->id)
                ->where(function ($query) {
                    return $query->where('tecnico_id', $this->tecnico_id);
                }),
            ],
            'cantidad' => ['required', 'numeric', 'regex:/^\d+(\.\d{2})?$/'],
            'fecha' => ['required', 'date'],
            'horas_facturadas' => ['required', 'integer'],
            'comentarios' => ['nullable', 'string'],
            'tecnico_id' => ['required', 'integer', 'exists:empleados,id'],
            'wo_id' => ['required', 'integer', 'exists:work_orders,id'],
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
