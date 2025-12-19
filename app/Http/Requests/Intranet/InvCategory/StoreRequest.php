<?php

namespace App\Http\Requests\Intranet\InvCategory;

use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:191',
                Rule::unique('inv_categories')
                    ->where('status_id', $this->status_id)
                    ->where('inv_group_id', $this->inv_group_id),
            ],
            'description' => ['nullable', 'string', 'max:191'],
            'status_id' => ['required', 'integer', 'exists:estatus,id'],
            'inv_group_id' => ['required', 'integer', 'exists:inv_groups,id'],
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
