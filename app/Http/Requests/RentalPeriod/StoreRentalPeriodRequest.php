<?php

namespace App\Http\Requests\RentalPeriod;

use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreRentalPeriodRequest extends FormRequest
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
        $today = Carbon::today();

        return [
            'folio' => ['required', 'string', 'max:50', 'unique:rental_periods,folio'],
            'start_date' => ['required', 'date', 'before_or_equal:end_date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'billing_day' => ['required', 'integer', 'min:1', 'max:31'],
            'rental_value' => ['required', 'numeric', 'min:0'],
            'comments' => ['nullable', 'string', 'max:500'],
            'base64' => ['nullable', 'string'],
            'empleado_id' => ['required', 'exists:empleados,id'],
            'cliente_id' => ['required', 'exists:clientes,id'],
            'rental_machine_id' => [
                'required',
                'exists:rental_machines,id',
                function ($attribute, $value, $fail) {
                    $startDate = $this->input('start_date');
                    $endDate = $this->input('end_date');

                    $conflictingPeriod = \App\Models\RentalPeriod::where('rental_machine_id', $value)
                        ->where(function ($query) use ($startDate, $endDate) {
                            $query->whereBetween('start_date', [$startDate, $endDate])
                                ->orWhereBetween('end_date', [$startDate, $endDate])
                                ->orWhere(function ($query) use ($startDate, $endDate) {
                                    $query->where('start_date', '<=', $startDate)
                                        ->where('end_date', '>=', $endDate);
                                });
                        })
                        ->exists();

                    if ($conflictingPeriod) {
                        $fail('The selected machine is already rented during the specified period.');
                    }
                },
            ],

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
