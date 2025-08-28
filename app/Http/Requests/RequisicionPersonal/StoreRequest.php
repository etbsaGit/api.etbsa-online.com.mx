<?php

namespace App\Http\Requests\RequisicionPersonal;

use App\Models\Departamento;
use App\Models\Puesto;
use App\Models\Empleado;
use Illuminate\Http\Response;
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
     * Preparar los datos antes de la validación.
     */
    protected function prepareForValidation()
    {
        $rh = Empleado::where('puesto_id', Puesto::where('nombre', 'Gerente corporativo')->value('id'))
            ->where('departamento_id', Departamento::where('nombre', 'Recursos Humanos')->value('id'))
            ->where('estatus_id', 5)
            ->first();

        $da = Empleado::where('puesto_id', Puesto::where('nombre', 'Director Administrativo')->value('id'))
            ->where('estatus_id', 5)
            ->first();

        $user = auth()->user();

        $this->merge([
            'solicita_id' => $user?->empleado?->id,
            'autoriza_id' => $user?->empleado?->jefe_directo_id,
            'vo_bo_id' => $da?->id,
            'recibe_id' => $rh?->id
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "base64" => ['nullable', 'string'],
            'sexo' => 'nullable|string|in:Masculino,Femenino,Ambos,Otro', // cambiado de array a string

            'rango_edad' => 'nullable|string', // cambiado de array a string

            'habilidades' => 'nullable|string',

            'idiomas' => 'nullable|array',
            'idiomas.*' => 'string',

            'manejo_equipo' => 'nullable|string',
            'sueldo_mensual_inicial' => 'nullable|numeric|min:0',
            'comisiones' => 'nullable|numeric|min:0',
            'experiencia_conocimientos' => 'nullable|string',
            'actividades_desempenar' => 'nullable|string',
            'total_posiciones' => 'required|numeric|min:1', // cambiado de integer a numeric para aceptar string numérico

            'tipo_vacante' => 'required|string|in:Remplazo,Nueva Creación,Temporal,Permanente',
            'motivo_vacante' => 'nullable|string',
            'especificar_vacante' => 'nullable|string',

            'puesto_id' => 'required|exists:puestos,id',
            'sucursal_id' => 'required|exists:sucursales,id',
            'linea_id' => 'nullable|exists:lineas,id',
            'departamento_id' => 'nullable|exists:departamentos,id',
            'escolaridad_id' => 'nullable|exists:escolaridades,id',

            'solicita_id' => 'nullable|exists:empleados,id',
            'autoriza_id' => 'nullable|exists:empleados,id',
            'vo_bo_id' => 'nullable|exists:empleados,id',
            'recibe_id' => 'nullable|exists:empleados,id',

            'autorizacion' => 'nullable|boolean',
            'estatus' => 'nullable|boolean',

            'competencias' => 'nullable|array',
            'competencias.*' => 'required|exists:competencias,id',

            'herramientas' => 'nullable|array',
            'herramientas.*' => 'required|exists:herramientas,id',
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
