<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Grade;
use App\Models\Puesto;
use App\Models\Survey;
use App\Models\Empleado;
use App\Mail\GradeMailable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Mail\SurveyMailable;
use App\Models\SurveyAnswer;
use Illuminate\Http\Request;
use App\Mail\EvalueeMailable;
use App\Models\SurveyQuestion;
use App\Traits\UploadableFile;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Survey\GradeRequest;
use App\Http\Requests\Survey\SurveyStoreRequest;
use App\Http\Requests\Survey\UpdateSurveyRequest;
use App\Http\Requests\Survey\EvalueesSurveyRequest;
use App\Http\Requests\Survey\StoreSurveyAnswerRequest;
use PDF;

class SurveyController extends ApiController
{
    use UploadableFile;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $surveys = Survey::with(['puesto', 'question', 'question.answer.evaluee', 'evaluee', 'evaluee.empleado'])
            ->withCount('evaluee')
            ->get();

        return response()->json($surveys);
    }

    public function getSurveyDataForSurvey(Survey $survey)
    {
        $evaluees = $survey->evaluee()->with('evaluee')->get();

        $surveyData = $evaluees->map(function ($evaluee) use ($survey) {
            $totalQuestions = $survey->question->count();
            $totalResponses = $this->getResponseCountForEvaluee($evaluee, $survey);

            return [
                'evaluee_name' => $evaluee->empleado->nombreCompleto, // Reemplaza 'nombre' por el nombre real del campo
                'total_questions' => $totalQuestions,
                'total_responses' => $totalResponses,
            ];
        });

        return response()->json($surveyData);
    }

    private function getResponseCountForEvaluee($evaluee, $survey)
    {
        return $evaluee->answer()->whereHas('question', function ($query) use ($survey) {
            $query->where('survey_id', $survey->id);
        })->count();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SurveyStoreRequest $request)
    {
        $data = $request->validated();

        $survey = Survey::create($data);

        if ($survey->evaluator) {
            $to_email = $survey->evaluator->email;

            $to_name = $survey->evaluator->name;

            $survey_title = $survey->title;

            // Datos a pasar a la vista del correo electrónico
            $correo = [
                'to_name' => $to_name,
                'survey_title' => $survey_title,
            ];

            // Enviar el correo electrónico con los datos y la vista
            Mail::to($to_email)->send(new SurveyMailable($correo));
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
        return response()->json($survey->load(['puesto', 'question']));
    }

    public function showPerEvaluee()
    {
        $user = Auth::user();

        $surveys = $user->evaluee()
            ->whereHas('evaluee', function ($query) {
                $query->where('status', 1);
            })
            ->with(['question', 'question.answer'])
            ->get();

        return response()->json($surveys);
    }

    public function showPerEvaluator()
    {
        $user = Auth::user();

        $surveys = $user->survey()->where('evaluator_id', $user->id)->with(['puesto', 'question', 'evaluee', 'evaluee.empleado'])->withCount('evaluee')->get();

        return response()->json($surveys);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSurveyRequest $request, Survey $survey)
    {

        if ($request->evaluator_id !== $survey->evaluator_id) {

            $data = $request->validated();

            // Update survey in the database
            $survey->update($data);

            $to_email = $survey->evaluator->email;

            $to_name = $survey->evaluator->name;

            $survey_title = $survey->title;

            $correo = [
                'to_name' => $to_name,
                'survey_title' => $survey_title,
            ];

            Mail::to($to_email)->send(new SurveyMailable($correo));
        }

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

    public function changeStatus(Survey $survey)
    {
        if ($survey->status == 1) {
            $survey->status = 0;
        } elseif ($survey->status == 0) {
            $survey->status = 1;
        }

        $survey->save();

        return response()->json(['mensaje' => 'Estado cambiado exitosamente']);
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
        } elseif (isset($data['imagen'])) {
            $base64 = $this->getImageAsBase64($data['imagen']);
            $relativePath  = $this->saveImage($base64, $surveyQuestion->default_path_folder);
            $base64 = $relativePath;
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

        $empleadosAsociados = $survey->evaluee()->pluck('evaluee_id')->toArray();

        $nuevosEmpleadoIds = array_diff($empleadoIds, $empleadosAsociados);

        $survey->evaluee()->sync($empleadoIds);

        $usuarios = User::whereIn('id', $nuevosEmpleadoIds)->get();

        foreach ($usuarios as $usuario) {
            $to_email = $usuario->email;
            $to_name = $usuario->name;
            $survey_title = $survey->title;
            $expire_date = $survey->expire_date;

            $correo = [
                'to_name' => $to_name,
                'survey_title' => $survey_title,
                'expire_date' => $expire_date,
            ];

            Mail::to($to_email)->send(new EvalueeMailable($correo));
        }

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

        $to_email = $grade->evaluee->email;

        $correo = [
            'score' => $grade->score,
            'correct' => $grade->correct,
            'incorrect' => $grade->incorrect,
            'unanswered' => $grade->unanswered,
            'comments' => $grade->comments,
            'evaluee' => $grade->evaluee->name,
            'survey' => $grade->survey->title
        ];


        Mail::to($to_email)->send(new GradeMailable($correo));

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

        // Calcular el promedio de los puntajes
        $totalScores = $grades->sum('score');
        $averageScore = $grades->isEmpty() ? 0 : $totalScores / $grades->count();

        // Devolver el promedio de los puntajes
        return response()->json([
            'grades' => $grades,
            'average_score' => $averageScore
        ]);
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

    public function cloneSurvey(Survey $survey)
    {
        $newSurvey = $survey->replicate();
        $newSurvey->save();

        $oldQuestions = $survey->question;

        foreach ($oldQuestions as $preguntaOriginal) {
            $nuevaPregunta = $preguntaOriginal->replicate();
            $nuevaPregunta->survey_id = $newSurvey->id; // Ajustar la clave foránea de la nueva pregunta
            $nuevaPregunta->save();
        }
    }

    public function getKardex()
    {
        // Obtener todas las encuestas que tienen puesto_id a través de la relación con Puesto
        $surveysWithPuesto = Puesto::whereHas('survey')->with('survey', 'survey.evaluee.empleado', 'survey.evaluee.grade')->get();

        // Obtener todas las encuestas que no tienen puesto_id
        $surveysWithoutPuesto = Survey::whereNull('puesto_id')->with('evaluee.empleado', 'evaluee.grade')->get();

        // Crear una respuesta con ambas colecciones
        $response = [
            'surveys_with_puesto' => $surveysWithPuesto,
            'surveys_without_puesto' => $surveysWithoutPuesto,
        ];

        // Devolver la respuesta en formato JSON
        return response()->json($response);
    }

    public function getKardexPerEvaluator()
    {
        $user = Auth::user();

        $surveysWithPuesto = Puesto::whereHas('survey', function ($query) use ($user) {
            $query->where('evaluator_id', $user->id);
        })
            ->with(['survey' => function ($query) use ($user) {
                $query->where('evaluator_id', $user->id);
                // Aquí puedes agregar otras condiciones si es necesario
            }, 'survey.evaluee.empleado', 'survey.evaluee.grade'])
            ->get();


        $surveysWithoutPuesto = Survey::whereNull('puesto_id')
            ->where('evaluator_id', $user->id)
            ->with('evaluee.empleado', 'evaluee.grade')
            ->get();

        $response = [
            'surveys_with_puesto' => $surveysWithPuesto,
            'surveys_without_puesto' => $surveysWithoutPuesto,
        ];

        return response()->json($response);
    }

    public function getPDFAnswers(Survey $survey)
    {
        $user = Auth::user();

        // Obtener las preguntas de la encuesta
        $questions = $survey->question; // Asegúrate de que 'questions' es el nombre correcto

        // Verificar si hay preguntas asociadas a la encuesta
        if ($questions->isEmpty()) {
            return response()->json(['message' => 'No questions found for this survey.'], 404);
        }

        // Obtener las respuestas del usuario para esas preguntas
        $questionIds = $questions->pluck('id');
        $answers = SurveyAnswer::whereIn('question_id', $questionIds)
                             ->where('evaluee_id', $user->id)
                             ->get();

        // Verificar si hay respuestas
        if ($answers->isEmpty()) {
            return response()->json(['message' => 'No answers found for this survey.'], 404);
        }

        // Agrupar respuestas por pregunta y formatear
        $questionsWithAnswers = $questions->map(function ($question) use ($answers) {
            // Filtrar respuestas para la pregunta actual
            $questionAnswers = $answers->where('question_id', $question->id);

            // Si hay exactamente una respuesta, usa esa respuesta directamente
            if ($questionAnswers->count() === 1) {
                $question->answers = $questionAnswers->first();
            } else {
                // Si hay múltiples respuestas (o ninguna), usa el array de respuestas
                $question->answers = $questionAnswers->values();
            }
            return $question;
        });

        // Calcular el promedio de respuestas correctas
        $totalQuestions = $questions->count();
        $correctAnswers = $answers->where('rating', 1)->count(); // Asumiendo que 'rating' es el campo que indica si la respuesta es correcta
        $averageRating = $totalQuestions ? ($correctAnswers / $totalQuestions) * 100 : 0;

        $data = [
            'employee' => $user->empleado,
            'survey' => $survey,
            'questions' => $questionsWithAnswers,
            'averageRating' => $averageRating, // Agregar calificación promedio a los datos
        ];

        // Cargar la vista y pasarle los datos
        $pdf = PDF::loadView('pdf.survey.answers', $data);

        // // Descargar el PDF generado comentar para produccion se utiliza para postman
        // return $pdf->download('document.pdf');

        // Obtener el contenido del PDF como cadena binaria
        $pdfContent = $pdf->output();

        // Convertir el contenido a Base64
        $pdfBase64 = base64_encode($pdfContent);

        // Retornar el PDF en Base64
        return $this->respond($pdfBase64);
    }



}
