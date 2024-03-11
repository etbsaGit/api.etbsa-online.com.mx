<?php

namespace App\Http\Controllers\Api\encuestas;

use App\Models\User;
use App\Models\Grade;
use App\Models\Survey;
use App\Models\Empleado;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\SurveyAnswer;
use Illuminate\Http\Request;
use App\Models\SurveyQuestion;
use App\Traits\UploadableFile;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Survey\GradeRequest;
use App\Http\Requests\Survey\SurveyStoreRequest;
use App\Http\Requests\Survey\UpdateSurveyRequest;
use App\Http\Requests\Survey\EvalueesSurveyRequest;
use App\Http\Requests\Survey\StoreSurveyAnswerRequest;

class SurveyController extends Controller
{
    use UploadableFile;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $surveys = Survey::with(['question', 'evaluee', 'evaluee.empleado'])
            ->withCount('evaluee')
            ->get();

        return response()->json($surveys);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SurveyStoreRequest $request)
    {
        $data = $request->validated();

        $survey = Survey::create($data);

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
        return response()->json($survey->load(['question']));
    }

    public function showPerEvaluee(User $userId)
    {
        $surveys = $userId->evaluee()
            ->whereHas('evaluee', function ($query) {
                $query->where('status', 1);
            })
            ->with(['question', 'question.answer'])
            ->get();

        return response()->json($surveys);
    }



    public function showPerEvaluator(User $userId)
    {
        $surveys = $userId->evaluee()->where('evaluator_id', $userId->id)->with(['question', 'evaluee', 'evaluee.empleado'])->withCount('evaluee')->get();

        return response()->json($surveys);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSurveyRequest $request, Survey $survey)
    {
        $data = $request->validated();

        // Update survey in the database
        $survey->update($data);

        // Get ids as plain array of existing questions
        $existingIds = $survey->question()->pluck('id')->toArray();
        // Get ids as plain array of new questions
        $newIds = Arr::pluck($data['questions'], 'id');
        // Find questions to delete
        $toDelete = array_diff($existingIds, $newIds);
        //Find questions to add
        $toAdd = array_diff($newIds, $existingIds);

        // Delete questions by $toDelete array
        foreach ($toDelete as $questionId) {
            $question = SurveyQuestion::find($questionId);
            if ($question) {
                // Eliminar la imagen asociada de S3
                if ($question->image) {
                    Storage::disk('s3')->delete($question->image);
                }
                // Eliminar la pregunta
                $question->delete();
            }
        }

        // Create new questions
        foreach ($data['questions'] as $question) {
            if (in_array($question['id'], $toAdd)) {
                $question['survey_id'] = $survey->id;
                $this->createQuestion($question);
            }
        }

        // Update existing questions
        $questionMap = collect($data['questions'])->keyBy('id');
        $questions = $survey->question;
        foreach ($questions as $question) {
            if (isset($questionMap[$question->id])) {
                $this->updateQuestion($question, $questionMap[$question->id]);
            }
        }

        return response()->json($survey->load('question'));
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

        $validator = Validator::make($data, [
            'question' => ['required', 'string'],
            'type' => ['required', Rule::in([
                Survey::TYPE_TEXT,
                Survey::TYPE_TEXTAREA,
                Survey::TYPE_SELECT,
                Survey::TYPE_RADIO,
                Survey::TYPE_CHECKBOX,
            ])],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'string'],
            'data' => ['present'],
            'survey_id' => ['exists:App\Models\Survey,id']
        ]);

        $surveyQuestion = SurveyQuestion::create($validator->validated());

        if (isset($data['base64'])) {
            $relativePath  = $this->saveImage($data['base64'], $surveyQuestion->default_path_folder);
            $data['base64'] = $relativePath;
            $updateData = ['image' => $relativePath];
            $surveyQuestion->update($updateData);
        }


        return $surveyQuestion;
    }

    private function updateQuestion(SurveyQuestion $surveyQuestion, $data)
    {
        $validator = Validator::make($data, [
            'id' => ['exists:App\Models\SurveyQuestion,id'],
            'question' => ['required', 'string'],
            'type' => ['required', Rule::in([
                Survey::TYPE_TEXT,
                Survey::TYPE_TEXTAREA,
                Survey::TYPE_SELECT,
                Survey::TYPE_RADIO,
                Survey::TYPE_CHECKBOX,
            ])],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'string'],
            'data' => ['present'],
        ]);

        $surveyQuestion->update($validator->validated());

        if (isset($data['base64'])) {
            if ($surveyQuestion->image) {
                Storage::disk('s3')->delete($surveyQuestion->image);
            }
            $relativePath  = $this->saveImage($data['base64'], $surveyQuestion->default_path_folder);
            $data['base64'] = $relativePath;
            $updateData = ['image' => $relativePath];
            $surveyQuestion->update($updateData);
        }
        return $surveyQuestion;
    }

    public function deleteImage(SurveyQuestion $surveyQuestion)
    {
        Storage::disk('s3')->delete($surveyQuestion->image);
        $surveyQuestion->update(['image' => null]);
        return response()->json(['success' => true, 'message' => 'Imagen eliminada correctamente']);
    }

    public function storeAnswer(StoreSurveyAnswerRequest $request)
    {
        $surveyAnswer = SurveyAnswer::create($request->validated());
        return response()->json($surveyAnswer);
    }


    public function getAnswers()
    {
        $answers = SurveyAnswer::with('question')->get();
        return response()->json($answers);
    }

    public function getAnswerUserForSurvey($surveyId, $userId)
    {
        $questions = Survey::find($surveyId)->question;

        $answers = SurveyAnswer::whereIn('question_id', $questions->pluck('id'))
            ->where('evaluee_id', $userId)
            ->get();

        return response()->json($answers);
    }

    function updateComment(SurveyAnswer $answer, Request $request)
    {
        $answer->comments = $request->comments;
        $answer->rating = $request->rating;

        $answer->save();

        return response()->json($answer);
    }

    public function storeEvaluees(Survey $survey, EvalueesSurveyRequest $request)
    {
        $empleadoIds = $request->evaluees;

        $survey->evaluee()->sync($empleadoIds);

        return response()->json(['message' => 'Empleados asignados correctamente a la encuesta']);
    }

    public function getEvaluees(Survey $survey)
    {
        $evaluees = $survey->evaluee()->with(['Empleado', 'Answer'])->get();

        return response()->json($evaluees);
    }

    public function storeGrade(GradeRequest $request)
    {
        $data = $request->validated(); // Obtener los datos validados del request

        if ($request->has('id')) {
            // Si hay un ID en la solicitud, actualizar la calificación existente
            $grade = Grade::findOrFail($request->id);
            $grade->update($data);
        } else {
            // Si no hay un ID en la solicitud, crear una nueva calificación
            $grade = Grade::create($data);
        }

        return response()->json($grade);
    }

    public function getForGrade(User $evaluee, Survey $survey)
    {
        // Obtener las calificaciones donde coincidan el survey_id y el evaluee_id
        $matchedGrades = Grade::where('survey_id', $survey->id)
            ->where('evaluee_id', $evaluee->id)
            ->get();

        // Obtener el total de preguntas de la encuesta
        $totalQuestions = $survey->question->count();

        // Obtener las preguntas respondidas por el evaluado para esta encuesta
        $answeredQuestions = DB::table('survey_answers')
            ->where('evaluee_id', $evaluee->id)
            ->whereIn('question_id', $survey->question->pluck('id'))
            ->pluck('question_id')
            ->unique();

        // Obtener las preguntas que no se respondieron
        $unansweredQuestionsIds = $survey->question->pluck('id')->diff($answeredQuestions);

        // Obtener los modelos de preguntas que no se respondieron
        $unansweredQuestions = SurveyQuestion::whereIn('id', $unansweredQuestionsIds)->get();

        // Contar el total de respuestas
        $totalResponses = $answeredQuestions->count();

        // Contar las respuestas correctas, incorrectas y no contestadas
        $correctResponses = DB::table('survey_answers')
            ->where('evaluee_id', $evaluee->id)
            ->whereIn('question_id', $survey->question->pluck('id'))
            ->where('rating', 1)
            ->count();

        $incorrectResponses = DB::table('survey_answers')
            ->where('evaluee_id', $evaluee->id)
            ->whereIn('question_id', $survey->question->pluck('id'))
            ->where('rating', 0)
            ->count();

        $ungradedResponses = DB::table('survey_answers')
            ->where('evaluee_id', $evaluee->id)
            ->whereIn('question_id', $survey->question->pluck('id'))
            ->whereNull('rating')
            ->count();

        $unansweredResponses = $totalQuestions - $totalResponses;

        // Calcular el promedio de respuestas correctas
        $averageGrade = $totalQuestions > 0 ? ($correctResponses / $totalQuestions) * 100 : 0;

        return response()->json([
            'total_questions' => $totalQuestions,
            'total_responses' => $totalResponses,
            'correct_responses' => $correctResponses,
            'incorrect_responses' => $incorrectResponses,
            'unanswered_responses' => $unansweredResponses,
            'ungraded_responses' => $ungradedResponses,
            'average_grade' => $averageGrade,
            'matched_grades' => $matchedGrades,
            'unanswered_questions' => $unansweredQuestions,
        ]);
    }


    public function getGradesForEvaluee(User $evaluee)
    {
        // Obtener todas las calificaciones para el evaluado especificado
        $grades = Grade::where('evaluee_id', $evaluee->id)->with('survey')->get();
        // Devolver las calificaciones en formato JSON
        return response()->json($grades);
    }

    public function getGradesForSurvey(Survey $survey)
    {
        // Obtener las calificaciones para la encuesta dada con los datos de los usuarios
        $grades = Grade::where('survey_id', $survey->id)->with('evaluee.empleado')->get();

        // Extraer los IDs únicos de los usuarios de las calificaciones
        $userIds = $grades->pluck('evaluee_id')->unique();

        // Obtener los usuarios correspondientes a los IDs únicos
        $users = User::whereIn('id', $userIds)->get();

        $unansweredQuestionsIds = collect(); // Inicialización de la colección de IDs no respondidos

        foreach ($users as $evaluee) {
            // Obtener las preguntas respondidas por el evaluado para esta encuesta
            $answeredQuestions = DB::table('survey_answers')
                ->where('evaluee_id', $evaluee->id)
                ->whereIn('question_id', $survey->question->pluck('id'))
                ->pluck('question_id')
                ->unique();

            // Obtener las preguntas que no se respondieron y agregarlas a la colección
            $unansweredQuestionsIds = $unansweredQuestionsIds->merge($survey->question->pluck('id')->diff($answeredQuestions));
        }

        // Obtener las preguntas y respuestas de la encuesta
        $questions = $survey->question()->with('answer')->get();
        $answers = $questions->flatMap->answer;

        return response()->json([
            'grades' => $grades,
            'users' => $users,
            'questions' => $questions,
            'answers' => $answers,
            'unanswered' => $unansweredQuestionsIds->values()->all() // Convertir la colección en un array
        ]);
    }



    private function saveImage($base64, $defaultPathFolder)
    {
        // Check if image is valid base64 string
        if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
            // Take out the base64 encoded text without mime type
            $image = substr($base64, strpos($base64, ',') + 1);
            // Get file extension
            $type = strtolower($type[1]); // jpg, png, gif

            // Check if file is an image
            if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                throw new \Exception('invalid image type');
            }
            $image = str_replace(' ', '+', $image);
            $image = base64_decode($image);

            if ($image === false) {
                throw new \Exception('base64_decode failed');
            }
        } else {
            throw new \Exception('did not match data URI with image data');
        }

        $fileName = Str::random() . '.' . $type;
        $filePath = $defaultPathFolder . '/' . $fileName;

        // Guardar el archivo en AWS S3
        Storage::disk('s3')->put($filePath, $image);

        return $filePath;
    }
}
