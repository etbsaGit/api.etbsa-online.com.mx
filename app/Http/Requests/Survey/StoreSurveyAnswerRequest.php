<?php

namespace App\Http\Requests\Survey;

use Illuminate\Foundation\Http\FormRequest;

class StoreSurveyAnswerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'answer' => ['required'],
            'comments'=>['nullable','string'],
            'rating'=>['nullable','integer'],
            'evaluee_id'=>['required','integer'],
            'question_id'=>['required','integer']
        ];
    }
}
