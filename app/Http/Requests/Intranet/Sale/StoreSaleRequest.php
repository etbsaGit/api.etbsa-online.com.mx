<?php

namespace App\Http\Requests\Intranet\Sale;

use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreSaleRequest extends FormRequest
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
            "serial" => ['nullable', 'string', 'max:255', 'unique:sales,serial,NULL,id,cancellation,0'],
            "invoice" => ['nullable', 'string', 'max:255', 'unique:sales,invoice,NULL,id,cancellation,0'],
            "order" => ['nullable', 'string', 'max:255', 'unique:sales,order,NULL,id,cancellation,0'],
            "folio" => ['nullable', 'string', 'max:255', 'unique:sales,folio,NULL,id,cancellation,0'],
            "economic" => ['nullable', 'string', 'max:255', 'unique:sales,economic,NULL,id,cancellation,0'],
            'validated' => ['nullable', 'boolean'],
            'date' => ['nullable', 'date'],
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'status_id' => ['required', 'integer', 'exists:estatus,id'],
            'referencia_id' => ['nullable', 'integer', 'exists:referencias,id'],
            'empleado_id' => ['required', 'integer', 'exists:empleados,id'],
            'sucursal_id' => ['required', 'integer', 'exists:sucursales,id'],
            'cancellation_date' => ['nullable', 'date'],
            "cancellation_folio" => ['nullable', 'string', 'max:255', 'unique:sales,cancellation_folio,NULL,id,cancellation,0'],
            'cancellation' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.numeric' => 'El campo monto debe ser un número.',
            'comments.string' => 'El campo comentarios debe ser una cadena de texto.',
            'comments.max' => 'El campo comentarios no puede tener más de 255 caracteres.',
            'serial.unique' => 'El número de serie ya está en uso.',
            'invoice.unique' => 'La factura ya está en uso.',
            'order.unique' => 'El pedido ya está en uso.',
            'folio.unique' => 'El folio ya está en uso.',
            'economic.unique' => 'El campo económico ya está en uso.',
            'validated.boolean' => 'El campo validado debe ser verdadero o falso.',
            'date.date' => 'El campo fecha debe ser una fecha válida.',
            'cliente_id.required' => 'El campo cliente es obligatorio.',
            'cliente_id.exists' => 'El cliente seleccionado no es válido.',
            'status_id.required' => 'El campo estado es obligatorio.',
            'status_id.exists' => 'El estado seleccionado no es válido.',
            'referencia_id.exists' => 'La referencia seleccionada no es válida.',
            'empleado_id.required' => 'El campo empleado es obligatorio.',
            'empleado_id.exists' => 'El empleado seleccionado no es válido.',
            'sucursal_id.required' => 'El campo sucursal es obligatorio.',
            'sucursal_id.exists' => 'La sucursal seleccionada no es válida.',
            'cancellation_date.date' => 'El campo fecha de cancelación debe ser una fecha válida.',
            'cancellation_folio.unique' => 'El folio de cancelación ya está en uso.',
            'cancellation.required' => 'El campo cancelación es obligatorio.',
            'cancellation.boolean' => 'El campo cancelación debe ser verdadero o falso.',
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
