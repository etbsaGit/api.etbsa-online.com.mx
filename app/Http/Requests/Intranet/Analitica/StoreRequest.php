<?php

namespace App\Http\Requests\Intranet\Analitica;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

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
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $user = Auth::user();

        // Solo modificar empleado_id si el usuario NO tiene el rol 'Credito'
        if (! $user->hasRole('Credito')) {
            $this->merge([
                'empleado_id' => optional($user->empleado)->id,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "titulo" => ['required', 'string', 'max:191'],
            'efectivo' => ['required', 'numeric', 'min:0'],
            'caja' => ['required', 'numeric', 'min:0'],
            'gastos' => ['nullable', 'numeric', 'min:0'],
            'documentospc' => ['nullable', 'numeric', 'min:0'],
            'mercancias' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'boolean'],
            'fecha' => ['required', 'date'],
            "comentarios" => ['nullable', 'string', 'max:191'],
            'cliente_id' => ['required', 'exists:clientes,id'],
            'empleado_id' => 'nullable|exists:empleados,id', // Puede ser null

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
