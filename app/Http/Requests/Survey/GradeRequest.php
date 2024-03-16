<?php

namespace App\Http\Requests\Survey;

use Illuminate\Foundation\Http\FormRequest;

class GradeRequest extends FormRequest
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
            'id'=>['nullable'],
            'comments' => ['required','string'],
            'score' => ['required'],
            'questions' => ['required','integer'],
            'correct' => ['required','integer'],
            'incorrect' => ['required','integer'],
            'unanswered' => ['required','integer'],
            'evaluee_id' => ['required','exists:users,id'],
            'survey_id' => ['required','exists:surveys,id'],
        ];
    }
}
