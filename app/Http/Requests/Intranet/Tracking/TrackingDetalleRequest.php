<?php

namespace App\Http\Requests\Intranet\Tracking;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TrackingDetalleRequest extends FormRequest{
    public function authorize():bool
    {
        return true;
    }

    public function rules(): array{
        return [
            'tracking_id' => ['required','exists:tracking,id'],
            'product_id' => ['required','exists:products,id'],
            'cantidad' => ['required','numeric'],
            'subtotal' => ['required','numeric'],
            'precio_unidad' => ['required','numeric'],
        ];
    }

    public function messages():array{
        return [
                'tracking_id.required' => 'El campo tracking es obligatorio.',
                'tracking_id.exists' => 'El tracking seleccionado no existe.',
                'product_id.required' => 'El campo producto es obligatorio.',
                'product_id.exists' => 'El producto seleccionado no existe.',
                'cantidad.required' => 'El campo cantidad es obligatorio.',
                'cantidad.numeric' => 'El campo cantidad debe ser un número.',
                'subtotal.required' => 'El campo subtotal es obligatorio.',
                'subtotal.numeric' => 'El campo subtotal debe ser un número.',
                'precio_unidad.required' => 'El campo precio por unidad es obligatorio.',
                'precio_unidad.numeric' => 'El campo precio por unidad debe ser un número.'
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
