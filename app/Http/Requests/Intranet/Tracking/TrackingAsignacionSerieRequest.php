<?php

namespace App\Http\Requests\Intranet\Tracking;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TrackingAsignacionSerieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 'tracking_id' => ['required', 'exists:tracking,id'],
            'inv_item_id' => ['required', 'exists:inv_items,id'],
            // 'asignado_por' => ['required', 'exists:empleados,id'],
            'comentarios' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'tracking_id.required' => 'El campo tracking es obligatorio.',
            'tracking_id.exists' => 'El tracking seleccionado no existe.',
            'inv_item_id.required' => 'Selecciona un producto para asignar.',
            'inv_item_id.exists' => 'El producto seleccionado no existe.',

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
