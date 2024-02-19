<?php

namespace App\Http\Controllers\Api\encuestas;

use App\Models\Survey;
use Illuminate\Http\Request;
use App\Models\SurveyQuestion;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Survey\SurveyStoreRequest;
use App\Traits\UploadableFile;
use Illuminate\Support\Facades\Storage;

class SurveyController extends Controller
{
    use UploadableFile;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Survey::with(['question'])->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SurveyStoreRequest $request)
    {
        $data = $request->validated();

        $survey = Survey::create($data);

        if ($request->hasFile('image')) {
            $pic = $request->file('image');
            $path = $this->uploadOne($pic, $survey->default_path_folder, 's3');
            $updateData = ['image' => $path];
            $survey->update($updateData);
        }

        // Create new questions
        foreach ($data['questions'] as $question) {
            $question['survey_id'] = $survey->id;
            $this->createQuestion($question);
        }

        return response()->json($survey);
    }

    /**
     * Display the specified resource.
     */
    public function show(Survey $survey)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Survey $survey)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Survey $survey)
    {
        if ($survey->image) {
            Storage::disk('s3')->delete($survey->image);
        }

        $survey->delete();

        return response('', 204);
    }

    private function createQuestion($data)
    {
        if (is_array($data['data'])) {
            $data['data'] = json_encode($data['data']);
        }

        $validator = Validator::make($data, [
            'question' => ['required','string'],
            'type' => ['required', Rule::in([
                Survey::TYPE_TEXT,
                Survey::TYPE_TEXTAREA,
                Survey::TYPE_SELECT,
                Survey::TYPE_RADIO,
                Survey::TYPE_CHECKBOX,
            ])],
            'description' => ['nullable','string'],
            'image' => ['nullable', 'string'],
            'data' => ['present'],
            'survey_id' => ['exists:App\Models\Survey,id']
        ]);

        $surveyQuestion = SurveyQuestion::create($validator->validated());

        if (isset($data['image'])) {
            $pic = $data['image'];
            $path = $this->uploadOne($pic, $surveyQuestion->default_path_folder, 's3');
            $updateData = ['image' => $path];
            $surveyQuestion->update($updateData);
        }

        return $surveyQuestion;
    }

    private function updateQuestion(SurveyQuestion $question, $data)
    {
        if (is_array($data['data'])) {
            $data['data'] = json_encode($data['data']);
        }
        $validator = Validator::make($data, [
            'id' => 'exists:App\Models\SurveyQuestion,id',
            'question' => 'required|string',
            'type' => ['required', Rule::in([
                Survey::TYPE_TEXT,
                Survey::TYPE_TEXTAREA,
                Survey::TYPE_SELECT,
                Survey::TYPE_RADIO,
                Survey::TYPE_CHECKBOX,
            ])],
            'description' => 'nullable|string',
            'data' => 'present',
        ]);

        return $question->update($validator->validated());
    }
}
