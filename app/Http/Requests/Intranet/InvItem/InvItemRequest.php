<?php

namespace App\Http\Requests\Intranet\InvItem;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class InvItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $raw = $this->purchase_cost;

        if (is_null($raw) || trim($raw) === '') {
            $this->merge([
                'purchase_cost' => null,
            ]);
            return;
        }

        $value = preg_replace('/[^\d.]/', '', $raw);

        $this->merge([
            'purchase_cost' => $value,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $item = $this->route('invItem');
        return [
            'inv_factory_id' => ['required', 'integer', 'exists:inv_factories,id',],
            'rd' => ['required', 'string', 'max:255', Rule::unique('inv_items', 'rd')->ignore($item?->id)],
            'shipping_date' => ['required', 'date'],
            'shipping_status' => ['nullable', 'boolean'],
            'invoice' => ['nullable', 'string', 'max:255', Rule::unique('inv_items', 'invoice')->ignore($item?->id)],
            's_n' => ['nullable', 'string', 'max:255', Rule::unique('inv_items', 's_n')->ignore($item?->id),],
            's_n_m' => ['nullable', 'string', 'max:255', Rule::unique('inv_items', 's_n_m')->ignore($item?->id),],
            'e_n' => ['nullable', 'string', 'max:255', Rule::unique('inv_items', 'e_n')->ignore($item?->id),],
            'financing' => ['nullable', 'integer'],
            'invoice_date' => ['nullable', 'date'],
            'purchase_cost' => ['nullable', 'numeric', 'min:0'],
            'is_paid' => ['nullable', 'boolean'],
            'paid_date' => ['nullable', 'date'],
            'gps' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
            'inv_model_id' => ['nullable', 'integer', 'exists:inv_models,id',],
            'sucursal_id' => ['required', 'integer', 'exists:sucursales,id',],
            'inv_configurations' => ['present', 'array'],
            'inv_configurations.*' => ['integer', 'exists:inv_configurations,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'inv_factory_id.exists' => 'El proveedor seleccionado no existe.',

            'rd.required' => 'El RD es obligatorio.',
            'rd.unique' => 'Este RD ya se encuentra registrado.',

            'shipping_date.required' => 'La fecha de envío es obligatoria.',
            'shipping_date.date' => 'La fecha de envío no es válida.',

            'invoice.unique' => 'Esta factura ya se encuentra registrada.',

            's_n.unique' => 'El número de serie ya se encuentra registrado.',

            's_n_m.unique' => 'El número de serie del motor ya se encuentra registrado.',

            'e_n.unique' => 'El número de equipo ya se encuentra registrado.',

            'invoice_date.date' => 'La fecha de la factura no es válida.',

            'purchase_cost.numeric' => 'El costo de compra debe ser numérico.',
            'purchase_cost.min' => 'El costo de compra no puede ser negativo.',

            'is_paid.boolean' => 'El campo pagado debe ser verdadero o falso.',
            'gps.boolean' => 'El campo GPS debe ser verdadero o falso.',

            'inv_model_id.exists' => 'El modelo seleccionado no existe.',
            'sucursal_id.exists' => 'La sucursal seleccionada no existe.',
            'sucursal_id.required' => 'La sucursal es obligatoria.',

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
