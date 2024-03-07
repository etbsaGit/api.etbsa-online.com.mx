<?php

namespace App\Http\Requests\Survey;

use Illuminate\Support\Str;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSurveyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // $survey = $this->route('survey');
        // if ($this->user()->id !== $survey->user_id) {
        //     return false;
        // }
        return true;
    }

    protected function prepareForValidation()
    {
        $title = $this->input('title');
        $slug = Str::slug($title);
        $this->merge([
            'slug' => $slug,
        ]);

        // $this->merge([
        //     'user_id' => $this->user()->id
        // ]);
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'image' => ['nullable', 'string'],
            'title' => ['required', 'string'],
            'slug' => ['required', 'string'],
            'evaluator_id' => ['nullable','exists:users,id'],
            'status' => ['required', 'boolean'],
            'description' => ['nullable', 'string'],
            'expire_date' => ['nullable', 'date', 'after:tomoroow'],
            'questions' => ['array'],
        ];
    }
}
