<?php

namespace App\Http\Requests\Intranet\Sale;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Si es PUT/PATCH habrá route('sale'), si es POST normalmente no.
        $sale = $this->route('sale');          // puede ser null
        $saleId = $sale?->id;                  // null en POST, id en PUT/PATCH

        // Helper para armar el unique con cancellation = 0 y con ignore sólo si hay id
        $uniqueNotCancelled = function () use ($saleId) {
            $rule = Rule::unique('sales')->where(fn ($query) => $query->where('cancellation', 0));

            if ($saleId) {
                $rule->ignore($saleId);
            }

            return $rule;
        };

        return [
            "amount" => ['nullable', 'numeric'],
            "comments" => ['nullable', 'string', 'max:255'],

            "serial" => ['nullable', 'string', 'max:255', $uniqueNotCancelled()],
            "invoice" => ['nullable', 'string', 'max:255', $uniqueNotCancelled()],
            "order" => ['nullable', 'string', 'max:255', $uniqueNotCancelled()],
            "folio" => ['nullable', 'string', 'max:255', $uniqueNotCancelled()],
            "economic" => ['nullable', 'string', $uniqueNotCancelled()],

            'validated' => ['nullable', 'boolean'],
            "feedback" => ['nullable', 'string', 'max:255'],
            'date' => ['nullable', 'date'],

            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'status_id' => ['required', 'integer', 'exists:estatus,id'],
            'referencia_id' => ['nullable', 'integer', 'exists:referencias,id'],
            'empleado_id' => ['required', 'integer', 'exists:empleados,id'],
            'sucursal_id' => ['required', 'integer', 'exists:sucursales,id'],

            'cancellation_date' => ['nullable', 'date'],
            "cancellation_folio" => ['nullable', 'string', 'max:255', $uniqueNotCancelled()],

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
            'sucursal_id.exists' => 'El campo sucursal seleccionado no es válido.',
            'cancellation_date.date' => 'El campo fecha de cancelación debe ser una fecha válida.',
            'cancellation_folio.unique' => 'El folio de cancelación ya está en uso.',
            'cancellation.required' => 'El campo cancelación es obligatorio.',
            'cancellation.boolean' => 'El campo cancelación debe ser verdadero o falso.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Errores de validación',
            'errors'  => $validator->errors()
        ], 422));
    }
}
