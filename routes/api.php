<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BayController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UsedController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\LineaController;
use App\Http\Controllers\Api\SkillController;
use App\Http\Controllers\Api\VisitController;
use App\Http\Controllers\Api\CareerController;
use App\Http\Controllers\Api\PuestoController;
use App\Http\Controllers\Api\SurveyController;
use App\Http\Controllers\Api\ArchivoController;
use App\Http\Controllers\Api\EstatusController;
use App\Http\Controllers\Api\PostDocController;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\EmpleadoController;
use App\Http\Controllers\Api\ProspectController;
use App\Http\Controllers\Api\SucursalController;
use App\Http\Controllers\Api\DocumentoController;
use App\Http\Controllers\Api\PlantillaController;
use App\Http\Controllers\Api\RequisitoController;
use App\Http\Controllers\Api\WorkOrderController;
use App\Http\Controllers\Api\AntiguedadController;
use App\Http\Controllers\Api\ExpedienteController;
use App\Http\Controllers\Api\IncapacityController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\TechnicianController;
use App\Http\Controllers\Api\EscolaridadController;
use App\Http\Controllers\Api\EstadoCivilController;
use App\Http\Controllers\Api\ProspectAgpController;
use App\Http\Controllers\Api\SkillRaitngController;
use App\Http\Controllers\Api\VacationDayController;
use App\Http\Controllers\Ecommerce\BrandController;
use App\Http\Controllers\Api\DepartamentoController;
use App\Http\Controllers\Api\RentalPeriodController;
use App\Http\Controllers\Api\TipoDeSangreController;
use App\Http\Controllers\Api\WorkOrderDocController;
use App\Http\Controllers\Ecommerce\VendorController;
use App\Http\Controllers\Api\ProspectRiegoController;
use App\Http\Controllers\Api\QualificationController;
use App\Http\Controllers\Api\RentalMachineController;
use App\Http\Controllers\Ecommerce\ProductController;
use App\Http\Controllers\Api\TechniciansLogController;
use App\Http\Controllers\Ecommerce\CategoryController;
use App\Http\Controllers\Ecommerce\FeaturesController;
use App\Http\Controllers\Api\HorasTechnicianController;
use App\Http\Controllers\Api\ProspectCultivoController;
use App\Http\Controllers\Api\ProspectMaquinaController;
use App\Http\Controllers\Api\ProspectServicioController;
use App\Http\Controllers\Api\ActivityTechnicianController;
use App\Http\Controllers\Api\TechniciansInvoiceController;
use App\Http\Controllers\Api\ProspectDistribucionController;
use App\Http\Controllers\Api\UsedDocController;

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

Route::middleware(['auth:sanctum', 'cors'])->group(function () {

    //--------------------Rutas All--------------------
    Route::get('antiguedad/all', [AntiguedadController::class, 'all']);
    Route::get('archivo/all', [ArchivoController::class, 'all']);
    Route::get('documento/all', [DocumentoController::class, 'all']);
    Route::get('escolaridad/all', [EscolaridadController::class, 'all']);
    Route::get('estadoCivil/all', [EstadoCivilController::class, 'all']);
    Route::get('expediente/all', [ExpedienteController::class, 'all']);
    Route::get('plantilla/all', [PlantillaController::class, 'all']);
    Route::get('tipoDeSangre/all', [TipoDeSangreController::class, 'all']);
    Route::get('linea/all', [LineaController::class, 'all']);
    Route::get('sucursal/all', [SucursalController::class, 'all']);
    Route::get('departamento/all', [DepartamentoController::class, 'all']);
    Route::post('users/all', [UserController::class, 'all']);
    Route::get('requisito/all', [RequisitoController::class, 'all']);
    Route::get('puesto/all', [PuestoController::class, 'all']);

    //--------------------Catalogos para empleados-------------------
    Route::post('departamentos', [DepartamentoController::class, 'index']);
    Route::post('sucursales', [SucursalController::class, 'index']);
    Route::post('lineas', [LineaController::class, 'index']);
    Route::post('requisitos', [RequisitoController::class, 'index']);
    Route::post('puestos/excel', [PuestoController::class, 'export']);
    Route::post('puestos', [PuestoController::class, 'index']);
    Route::apiResource('departamento', DepartamentoController::class);
    Route::apiResource('sucursal', SucursalController::class);
    Route::apiResource('linea', LineaController::class);
    Route::apiResource('requisito', RequisitoController::class);
    Route::apiResource('puesto', PuestoController::class);

    //--------------------Estatus--------------------
    Route::post('estatuses', [EstatusController::class, 'index']);
    Route::get('estatus/{tipo}', [EstatusController::class, 'getPerType']);

    //--------------------Empleado--------------------
    Route::get('empleado/baja/{anio?}/{mes?}', [EmpleadoController::class, 'getEmployeesTerminations']);
    Route::get('empleado/alta/{anio?}/{mes?}', [EmpleadoController::class, 'getEmployeesNew']);

    Route::get('empleado/forms', [EmpleadoController::class, 'getforms']);
    Route::post('empleado/negocios', [EmpleadoController::class, 'negocios']);
    Route::get('empleado/index', [EmpleadoController::class, 'getformsIndex']);
    Route::post('empleados', [EmpleadoController::class, 'index']);
    Route::post('empleados/excel', [EmpleadoController::class, 'export']);
    Route::post('empleados/excel/vacations', [EmpleadoController::class, 'exportVacations']);
    Route::post('empleados/vacations', [EmpleadoController::class, 'getVacations']);
    Route::apiResource('empleado', EmpleadoController::class);

    //--------------------Expediente--------------------
    Route::get('/buscar-expediente/{tipoModelo}/{idModelo}', [ExpedienteController::class, 'buscarExpedientePorArchivable']);

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
    Route::get('surveys/kardex', [SurveyController::class, 'getKardex']);
    Route::get('surveys/kardex/evaluator', [SurveyController::class, 'getKardexPerEvaluator']);
    Route::get('grades/{evaluee}', [SurveyController::class, 'getGradesForEvaluee']);
    Route::get('grades/survey/{survey}', [SurveyController::class, 'getGradesForSurvey']);
    Route::get('survey/pdf/answers/{survey}', [SurveyController::class, 'getPDFAnswers']);
    Route::apiResource('survey', SurveyController::class);

    //--------------------Technician--------------------
    Route::post('technician/linea/{technician}', [TechnicianController::class, 'storeLineas']);
    Route::post('technician/userx/{empleado}', [TechnicianController::class, 'setUserX']);
    Route::post('technician/productividad/{empleado}', [TechnicianController::class, 'setProductivity']);
    Route::post('technician/empleado/{empleado}/{technician}', [TechnicianController::class, 'changeTypeTechnician']);
    Route::get('technicians', [TechnicianController::class, 'getAll']);
    Route::get('technicians/{linea}', [TechnicianController::class, 'getTechnicianLine']);
    Route::get('technician/all', [QualificationController::class, 'getEmployeeTechnician']);
    Route::get('qualifications/{linea}', [QualificationController::class, 'getPerLine']);
    Route::post('qualifications/empleado/{empleado}', [QualificationController::class, 'storeQualifications']);
    Route::get('bays/forms', [BayController::class, 'getAllData']);
    Route::get('bays/tech/{sucursal}/{linea}', [BayController::class, 'getTechData']);

    Route::get('pantalla/agricola/{sucursal}', [BayController::class, 'pantallaAgricola']);
    Route::get('pantalla/construccion/{sucursal}', [BayController::class, 'pantallaConstruccion']);

    Route::get('tech', [BayController::class, 'getTech']);
    Route::get('tech/disponibility', [BayController::class, 'getDisponibility']);
    Route::get('tech/calendar', [BayController::class, 'getCalendar']);

    Route::get('horasTechnician/tech/{id}/{anio}', [HorasTechnicianController::class, 'getPerTech']);

    Route::get('techniciansInvoice/wo/{empleado}', [TechniciansInvoiceController::class, 'getWoPerTech']);
    Route::post('techniciansInvoice/empleado', [TechniciansInvoiceController::class, 'getPerTech']);
    Route::get('techniciansLog/options/{empleado?}', [TechniciansLogController::class, 'getOptions']);
    Route::get('techniciansLog/tech/{empleado}', [TechniciansLogController::class, 'getPerTech']);
    Route::get('techniciansLog/techday/{empleado}/{day}', [TechniciansLogController::class, 'getPerTechDay']);

    Route::post('bays/getAll', [BayController::class, 'getAll']);
    Route::apiResource('qualification', QualificationController::class);
    Route::apiResource('technician', TechnicianController::class);
    Route::apiResource('bay', BayController::class);
    Route::apiResource('horasTechnician', HorasTechnicianController::class);
    Route::apiResource('activityTechnician', ActivityTechnicianController::class);
    Route::apiResource('techniciansInvoice', TechniciansInvoiceController::class);
    Route::apiResource('techniciansLog', TechniciansLogController::class);

    //--------------------Bays--------------------
    Route::get('bays/sucursal', [BayController::class, 'getSucursal']);


    //--------------------WorkOrder--------------------
    Route::get('wos/getform', [WorkOrderController::class, 'getForm']);
    Route::post('wos/getAll', [WorkOrderController::class, 'getWOS']);
    Route::apiResource('workOrder', WorkOrderController::class);
    Route::apiResource('workOrderDoc', WorkOrderDocController::class);

    //--------------------Skill--------------------
    Route::post('skills', [SkillController::class, 'index']);
    Route::get('skill/puesto/{puesto}', [SkillController::class, 'getPerPuesto']);
    Route::apiResource('skill', SkillController::class);

    //--------------------SkillRating--------------------
    Route::get('skillratings/{empleado}', [SkillRaitngController::class, 'getPerEmpleado']);
    Route::put('skillratings', [SkillRaitngController::class, 'saveSkillRating']);
    Route::apiResource('skillrating', SkillRaitngController::class);

    //--------------------Resource--------------------
    Route::resource('escolaridad', EscolaridadController::class)->except("create", "edit");
    Route::resource('estadoCivil', EstadoCivilController::class)->except("create", "edit");
    Route::resource('Estatus', EstatusController::class)->except("create", "edit");
    Route::resource('expediente', ExpedienteController::class)->except("create", "edit");
    Route::resource('plantilla', PlantillaController::class)->except("create", "edit");
    Route::resource('tipoDeSangre', TipoDeSangreController::class)->except("create", "edit");
    Route::resource('estatus', EstatusController::class)->except("create", "edit");
    Route::resource('user', UserController::class)->except("create", "edit");
    Route::resource('antiguedad', AntiguedadController::class)->except("create", "edit");
    Route::resource('archivo', ArchivoController::class)->except("create", "edit");
    Route::resource('documento', DocumentoController::class)->except("create", "edit");

    //--------------------User--------------------

    Route::post('auth/logout', [UserController::class, 'logout']);
    Route::post('auth/change', [UserController::class, 'changePassword']);
    Route::get('user/role/permission/all', [UserController::class, 'getRolesPermissions']);

    //--------------------landingPage/admin--------------------
    Route::get('formProduct', [ProductController::class, 'formProduct']);
    Route::put('product/active/{product}', [ProductController::class, 'changeActive']);
    Route::put('product/featured/{product}', [ProductController::class, 'changeFeatured']);
    Route::delete('product/image/{productImage}', [ProductController::class, 'deleteImg']);
    Route::put('categorie/{category}', [CategoryController::class, 'update']);
    Route::delete('categorie/{category}', [CategoryController::class, 'destroy']);
    Route::apiResource('brand', BrandController::class);
    Route::apiResource('vendor', VendorController::class);
    Route::apiResource('categorie', CategoryController::class);
    Route::apiResource('feature', FeaturesController::class);
    Route::apiResource('product', ProductController::class);

    //--------------------Calendar--------------------
    Route::get('event/{day}', [EventController::class, 'getPerDay']);
    Route::put('event/change/{event}', [EventController::class, 'changeDay']);
    Route::put('event/all', [EventController::class, 'getAll']);
    Route::put('event/child/{event}', [EventController::class, 'setEvent']);
    Route::put('event/clone/{event}', [EventController::class, 'cloneEvent']);
    Route::get('event/quit/{event}', [EventController::class, 'quitEvent']);
    Route::put('event/completed/{activity}', [EventController::class, 'changeCompleted']);
    Route::put('event/activity/{event}', [EventController::class, 'storeActivitiesEvent']);
    Route::get('activity/{event}', [ActivityController::class, 'showPerEvent']);
    Route::get('activity/form/{event}', [ActivityController::class, 'getEmployees']);
    Route::get('event/kardex/get/{anio?}/{mes?}', [EventController::class, 'getKardex']);
    Route::delete('event/travel/delete/{travel}', [EventController::class, 'destroyTravel']);
    Route::apiResource('events', EventController::class);
    Route::apiResource('activities', ActivityController::class);

    //--------------------Post--------------------
    Route::post('posts', [PostController::class, 'index']);
    Route::get('posts/forms', [PostController::class, 'getforms']);
    Route::post('posts/all', [PostController::class, 'getAll']);
    Route::get('posts/auth', [PostController::class, 'getPerAuth']);
    Route::get('posts/gen', [PostController::class, 'getPostsWithNullRelations']);
    Route::apiResource('post', PostController::class);
    Route::apiResource('postDoc', PostDocController::class);

    //--------------------RentalMachine--------------------
    Route::post('rentalMachines', [RentalMachineController::class, 'index']);
    Route::get('rentalMachines/all', [RentalMachineController::class, 'getAll']);
    Route::apiResource('rentalMachine', RentalMachineController::class);

    //--------------------RentalPeriod--------------------
    Route::post('rentalPeriods', [RentalPeriodController::class, 'index']);
    Route::get('rentalPeriods/all', [RentalPeriodController::class, 'getPerCalendar']);
    Route::get('rentalPeriods/mail/{rentalPeriod}', [RentalPeriodController::class, 'sendNotify']);
    Route::apiResource('rentalPeriod', RentalPeriodController::class);

    //--------------------Vacations--------------------
    Route::post('vacationDays', [VacationDayController::class, 'index']);
    Route::post('vacationDays/auth', [VacationDayController::class, 'myIndex']);
    Route::post('vacationDay/storeOnly', [VacationDayController::class, 'storeOnly']);
    Route::get('vacationDay/forms', [VacationDayController::class, 'getforms']);
    Route::get('vacationDay/on/{vacationDay}', [VacationDayController::class, 'setValidatedOn']);
    Route::get('vacationDay/off/{vacationDay}', [VacationDayController::class, 'setValidatedOff']);
    Route::get('vacationDay/calendar/{date}', [VacationDayController::class, 'getVacationCalendar']);
    Route::post('vacationDay/report', [VacationDayController::class, 'getReport']);
    Route::post('vacationDay/reportPDF', [VacationDayController::class, 'exportReport']);
    Route::apiResource('vacationDay', VacationDayController::class);

    //--------------------Incapacity--------------------
    Route::post('incapacities', [IncapacityController::class, 'index']);
    Route::get('incapacity/forms', [IncapacityController::class, 'getforms']);
    Route::get('incapacity/calendar/{date}', [IncapacityController::class, 'getIncapacityCalendar']);
    Route::apiResource('incapacity', IncapacityController::class);

    //--------------------Visits--------------------
    Route::post('visits', [VisitController::class, 'index']);
    Route::post('visits/report', [VisitController::class, 'getReport']);
    Route::post('visits/reportPDF', [VisitController::class, 'getReportPdf']);
    Route::get('visits/formReport', [VisitController::class, 'getFormReport']);
    Route::get('visit/forms', [VisitController::class, 'getforms']);
    Route::post('visit/kardex', [VisitController::class, 'getEmployeesWithVisits']);
    Route::get('visit/calendar/{date}', [VisitController::class, 'getVisitCalendar']);
    Route::apiResource('visit', VisitController::class);

    //--------------------Prospect--------------------
    Route::post('prospects', [ProspectController::class, 'index']);
    Route::get('prospect/forms', [ProspectController::class, 'getforms']);
    Route::apiResource('prospect', ProspectController::class);

    //--------------------ProspectCultivo--------------------
    Route::get('prospectCultivo/prospect/{prospect}', [ProspectCultivoController::class, 'getPerProspect']);
    Route::get('prospectCultivo/options', [ProspectCultivoController::class, 'getOptions']);
    Route::apiResource('prospectCultivo', ProspectCultivoController::class);

    //--------------------ProspectRiego--------------------
    Route::get('prospectRiego/prospect/{prospect}', [ProspectRiegoController::class, 'getPerProspect']);
    Route::apiResource('prospectRiego', ProspectRiegoController::class);

    //--------------------ProspectDictribucion--------------------
    Route::get('prospectDistribucion/prospect/{prospect}', [ProspectDistribucionController::class, 'getPerProspect']);
    Route::apiResource('prospectDistribucion', ProspectDistribucionController::class);

    //--------------------ProspectAgp--------------------
    Route::get('prospectAgp/prospect/{prospect}', [ProspectAgpController::class, 'getPerProspect']);
    Route::apiResource('prospectAgp', ProspectAgpController::class);

    //--------------------Prospectservicio--------------------
    Route::get('prospectServicio/prospect/{prospect}', [ProspectServicioController::class, 'getPerProspect']);
    Route::apiResource('prospectServicio', ProspectServicioController::class);

    //--------------------ProspectMaquina--------------------
    Route::get('prospectMaquina/prospect/{prospect}', [ProspectMaquinaController::class, 'getPerProspect']);
    Route::get('prospectMaquina/options', [ProspectMaquinaController::class, 'getOptions']);
    Route::apiResource('prospectMaquina', ProspectMaquinaController::class);

    //--------------------Used--------------------
    Route::post('useds', [UsedController::class, 'index']);
    Route::get('used/forms', [UsedController::class, 'getforms']);
    Route::apiResource('used', UsedController::class);
    Route::apiResource('usedDoc', UsedDocController::class);
});
//--------------------landingPage--------------------
Route::post('page/product/filter', [ProductController::class, 'filterProduct']);
Route::get('page/product/all', [ProductController::class, 'getAll']);
Route::post('page/product/get', [ProductController::class, 'getProducts']);
Route::get('page/product/random/{limit}', [ProductController::class, 'getRandomFeaturedProducts']);
Route::apiResource('page/brands', BrandController::class);
Route::apiResource('page/vendors', VendorController::class);
Route::apiResource('page/categories', CategoryController::class);
Route::apiResource('page/features', FeaturesController::class);
Route::apiResource('page/products', ProductController::class);

//--------------------Sin inicio de sesion--------------------
Route::post('documento/uploadFile/{documento}', [DocumentoController::class, 'uploadFile']);

//--------------------User--------------------
Route::post('auth/login', [UserController::class, 'login']);
Route::post('roles', [RoleController::class, 'index']);
Route::post('permissions', [PermissionController::class, 'index']);

Route::apiResource('role', RoleController::class);
Route::apiResource('permission', PermissionController::class);



//--------------------Expediente--------------------
Route::get('empleado/archivos/{rfc}/{ine}', [EmpleadoController::class, 'findEmpleadoByRFCandINE']);
