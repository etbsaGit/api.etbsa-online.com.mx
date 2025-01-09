<?php

namespace App\Http\Requests\Event;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreEventRequest extends FormRequest
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
        $empleado = Auth::user()->empleado->id;
        $this->merge([
            'empleado_id' => $empleado
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'date' => ['required', 'date'],
            'available_seats' => ['required', 'integer'],
            'empleado_id' => ['required', 'exists:empleados,id'],
            'travels' => ['required', 'array'],
            'travels.*.start_point' => ['nullable', 'exists:sucursales,id'],
            'travels.*.end_point' => ['nullable', 'exists:sucursales,id'],
            'travels.*.start_time' => ['required', 'date_format:H:i'],
            'travels.*.end_time' => ['required', 'date_format:H:i'],
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
