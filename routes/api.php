<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\LineaController;
use App\Http\Controllers\Api\CareerController;
use App\Http\Controllers\Api\PuestoController;
use App\Http\Controllers\Api\AlergiaController;
use App\Http\Controllers\Api\ArchivoController;
use App\Http\Controllers\Api\EscuelaController;
use App\Http\Controllers\Api\EstatusController;
use App\Http\Controllers\Api\EstudioController;
use App\Http\Controllers\Api\EmpleadoController;
use App\Http\Controllers\Api\SucursalController;
use App\Http\Controllers\Api\DocumentoController;
use App\Http\Controllers\Api\PlantillaController;
use App\Http\Controllers\Api\RequisitoController;
use App\Http\Controllers\Api\AntiguedadController;
use App\Http\Controllers\Api\AsignacionController;
use App\Http\Controllers\Api\EnfermedadController;
use App\Http\Controllers\Api\ExpedienteController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\EscolaridadController;
use App\Http\Controllers\Api\EstadoCivilController;
use App\Http\Controllers\Api\MedicamentoController;
use App\Http\Controllers\Api\ConstelacionController;
use App\Http\Controllers\Api\DepartamentoController;
use App\Http\Controllers\Api\TipoDeSangreController;
use App\Http\Controllers\Api\QualificationController;
use App\Http\Controllers\Api\DesvinculacionController;
use App\Http\Controllers\Api\EstadoDeEstudioController;
use App\Http\Controllers\Api\SurveyController;
use App\Http\Controllers\Api\TipoDeAsignacionController;
use App\Http\Controllers\Api\DocumentoQueAvalaController;
use App\Http\Controllers\Api\ExperienciaLaboralController;
use App\Http\Controllers\Api\ReferenciaPersonalController;
use App\Http\Controllers\Api\SkillController;
use App\Http\Controllers\Api\SkillRaitngController;
use App\Http\Controllers\Api\TechnicianController;
use App\Http\Controllers\Api\TipoDeDesvinculacionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'auth:sanctum'], function () {

    //--------------------Rutas All--------------------
    Route::get('alergia/all', [AlergiaController::class, 'all']);
    Route::get('antiguedad/all', [AntiguedadController::class, 'all']);
    Route::get('archivo/all', [ArchivoController::class, 'all']);
    Route::get('asignacion/all', [AsignacionController::class, 'all']);
    Route::get('constelacion/all', [ConstelacionController::class, 'all']);
    Route::get('departamento/all', [DepartamentoController::class, 'all']);
    Route::get('desvinculacion/all', [DesvinculacionController::class, 'all']);
    Route::get('documento/all', [DocumentoController::class, 'all']);
    Route::get('documentoQueAvala/all', [DocumentoQueAvalaController::class, 'all']);
    Route::get('empleado/all', [EmpleadoController::class, 'all']);
    Route::get('enfermedad/all', [EnfermedadController::class, 'all']);
    Route::get('escolaridad/all', [EscolaridadController::class, 'all']);
    Route::get('escuela/all', [EscuelaController::class, 'all']);
    Route::get('estadoCivil/all', [EstadoCivilController::class, 'all']);
    Route::get('estadoDeEstudio/all', [EstadoDeEstudioController::class, 'all']);
    Route::get('Estatus/all', [EstatusController::class, 'all']);
    Route::get('estudio/all', [EstudioController::class, 'all']);
    Route::get('expediente/all', [ExpedienteController::class, 'all']);
    Route::get('experienciaLaboral/all', [ExperienciaLaboralController::class, 'all']);
    Route::get('linea/all', [LineaController::class, 'all']);
    Route::get('medicamento/all', [MedicamentoController::class, 'all']);
    Route::get('plantilla/all', [PlantillaController::class, 'all']);
    Route::get('puesto/all', [PuestoController::class, 'all']);
    Route::get('referenciaPersonal/all', [ReferenciaPersonalController::class, 'all']);
    Route::get('requisito/all', [RequisitoController::class, 'all']);
    Route::get('sucursal/all', [SucursalController::class, 'all']);
    Route::get('tipoDeAsignacion/all', [TipoDeAsignacionController::class, 'all']);
    Route::get('tipoDeDesvinculacion/all', [TipoDeDesvinculacionController::class, 'all']);
    Route::get('tipoDeSangre/all', [TipoDeSangreController::class, 'all']);
    Route::get('estatus/all', [EstatusController::class, 'all']);
    Route::get('user/all', [UserController::class, 'all']);

    //--------------------Empleado--------------------
    Route::post('empleado/uploadPicture/{empleado}', [EmpleadoController::class, 'uploadPicture']);
    Route::post('empleado/filtertwo', [EmpleadoController::class, 'filtertwo']);
    Route::post('empleado/negocios', [EmpleadoController::class, 'modeloNegocio']);
    Route::get('empleado/personal', [EmpleadoController::class, 'personal']);

    //--------------------Expediente--------------------
    Route::get('/buscar-expediente/{tipoModelo}/{idModelo}', [ExpedienteController::class, 'buscarExpedientePorArchivable']);
    Route::post('documento/uploadFile/{documento}', [DocumentoController::class, 'uploadFile']);

    //--------------------Career--------------------
    Route::get('career/empleado/{empleado}', [CareerController::class, 'showPerEmpleado']);
    Route::get('career/empleados', [CareerController::class, 'empleadosWithAndWithoutCareer']);
    Route::get('career/empleados/new/{empleado}', [CareerController::class, 'storeNewCareer']);
    Route::resource('career', CareerController::class)->except("create", "edit");

    //--------------------Survey--------------------
    Route::get('survey/user', [SurveyController::class, 'showPerEvaluee']);
    Route::get('survey/evaluator', [SurveyController::class, 'showPerEvaluator']);
    Route::put('survey/status/{survey}', [SurveyController::class, 'changeStatus']);
    Route::post('survey/clone/{survey}', [SurveyController::class, 'cloneSurvey']);
    Route::post('surveys/evaluees/{survey}', [SurveyController::class, 'storeEvaluees']);
    Route::get('surveys/evaluees/{survey}', [SurveyController::class, 'getEvaluees']);
    Route::get('survey/answers/all', [SurveyController::class, 'getAnswers']);
    Route::get('survey/evaluees/all/{survey}', [SurveyController::class, 'getSurveyDataForSurvey']);
    Route::delete('survey/question/image/{surveyQuestion}', [SurveyController::class, 'deleteImage']);
    Route::post('survey/answer', [SurveyController::class, 'storeAnswer']);
    Route::get('survey/answer/{surveyId}/{userId}', [SurveyController::class, 'getAnswerUserForSurvey']);
    Route::put('survey/answer/{answer}', [SurveyController::class, 'updateComment']);
    Route::post('surveys/grade', [SurveyController::class, 'storeGrade']);
    Route::get('surveys/grade/{evaluee}/{survey}', [SurveyController::class, 'getForGrade']);
    Route::get('grades/{evaluee}', [SurveyController::class, 'getGradesForEvaluee']);
    Route::get('grades/survey/{survey}', [SurveyController::class, 'getGradesForSurvey']);
    Route::apiResource('survey', SurveyController::class);

    //--------------------Qualification--------------------
    Route::post('technician/linea/{technician}', [TechnicianController::class, 'storeLineas']);
    Route::post('technician/empleado/{empleado}/{technician}', [TechnicianController::class, 'changeTypeTechnician']);
    Route::get('technicians', [TechnicianController::class, 'getAll']);
    Route::get('technicians/{linea}', [TechnicianController::class, 'getTechnicianLine']);
    Route::get('technician/all', [QualificationController::class, 'getEmployeeTechnician']);
    Route::get('qualifications/{linea}', [QualificationController::class, 'getPerLine']);
    Route::post('qualifications/empleado/{empleado}', [QualificationController::class, 'storeQualifications']);
    Route::apiResource('qualification', QualificationController::class);
    Route::apiResource('technician', TechnicianController::class);

    //--------------------Skill--------------------
    Route::get('skill/puesto/{puesto}', [SkillController::class, 'getPerPuesto']);
    Route::apiResource('skill', SkillController::class);

    //--------------------SkillRating--------------------
    Route::get('skillratings/{empleado}', [SkillRaitngController::class, 'getPerEmpleado']);
    Route::put('skillratings', [SkillRaitngController::class, 'saveSkillRating']);
    Route::apiResource('skillrating', SkillRaitngController::class);

    //--------------------Resource--------------------
    Route::resource('documentoQueAvala', DocumentoQueAvalaController::class)->except("create", "edit");
    Route::resource('empleado', EmpleadoController::class)->except("create", "edit");
    Route::resource('enfermedad', EnfermedadController::class)->except("create", "edit");
    Route::resource('escolaridad', EscolaridadController::class)->except("create", "edit");
    Route::resource('escuela', EscuelaController::class)->except("create", "edit");
    Route::resource('estadoCivil', EstadoCivilController::class)->except("create", "edit");
    Route::resource('estadoDeEstudio', EstadoDeEstudioController::class)->except("create", "edit");
    Route::resource('Estatus', EstatusController::class)->except("create", "edit");
    Route::resource('estudio', EstudioController::class)->except("create", "edit");
    Route::resource('expediente', ExpedienteController::class)->except("create", "edit");
    Route::resource('experienciaLaboral', ExperienciaLaboralController::class)->except("create", "edit");
    Route::resource('linea', LineaController::class)->except("create", "edit");
    Route::resource('medicamento', MedicamentoController::class)->except("create", "edit");
    Route::resource('plantilla', PlantillaController::class)->except("create", "edit");
    Route::resource('puesto', PuestoController::class)->except("create", "edit");
    Route::resource('referenciaPersonal', ReferenciaPersonalController::class)->except("create", "edit");
    Route::resource('requisito', RequisitoController::class)->except("create", "edit");
    Route::resource('sucursal', SucursalController::class)->except("create", "edit");
    Route::resource('tipoDeAsignacion', TipoDeAsignacionController::class)->except("create", "edit");
    Route::resource('tipoDeDesvinculacion', TipoDeDesvinculacionController::class)->except("create", "edit");
    Route::resource('tipoDeSangre', TipoDeSangreController::class)->except("create", "edit");
    Route::resource('estatus', EstatusController::class)->except("create", "edit");
    Route::resource('user', UserController::class)->except("create", "edit");
    Route::resource('alergia', AlergiaController::class)->except("create", "edit");
    Route::resource('antiguedad', AntiguedadController::class)->except("create", "edit");
    Route::resource('archivo', ArchivoController::class)->except("create", "edit");
    Route::resource('asignacion', AsignacionController::class)->except("create", "edit");
    Route::resource('constelacion', ConstelacionController::class)->except("create", "edit");
    Route::resource('departamento', DepartamentoController::class)->except("create", "edit");
    Route::resource('desvinculacion', DesvinculacionController::class)->except("create", "edit");
    Route::resource('documento', DocumentoController::class)->except("create", "edit");

    //--------------------User--------------------

    Route::post('auth/logout', [UserController::class, 'logout']);
    Route::get('user/role/permission/all', [UserController::class, 'getRolesPermissions']);
});

//--------------------Sin inicio de sesion--------------------

//--------------------User--------------------
Route::post('auth/login', [UserController::class, 'login']);
Route::apiResource('role', RoleController::class);
Route::apiResource('permission', PermissionController::class);

//--------------------Expediente--------------------
Route::get('empleado/archivos/{rfc}/{ine}', [EmpleadoController::class, 'findEmpleadoByRFCandINE']);
