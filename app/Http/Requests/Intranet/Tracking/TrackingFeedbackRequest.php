<?php

namespace App\Http\Requests\Intranet\Tracking;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Override;

class TrackingFeedbackRequest extends FormRequest{
    public function authorize():bool
    {
        return true;
    }

    public function rules(): array{
        return [
            'comentario' => ['nullable'],
        ];
    }

    #[Override]
    public function messages():array
    {
        return [

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
