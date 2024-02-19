<?php

namespace App\Http\Requests\Empleado;

use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class PutRequest extends FormRequest
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
            "nombre" => ['required', 'string', 'max:255'],
            "segundo_nombre" => ['nullable', 'string', 'max:255'],
            "apellido_paterno" => ['required', 'string', 'max:255'],
            "apellido_materno" => ['required', 'string', 'max:255'],
            "fecha_de_nacimiento" => ['nullable', 'date'],
            "telefono" => ['nullable', 'numeric', 'digits:10', Rule::unique('empleados')->ignore($this->route("empleado")->id)],
            "telefono_institucional"=>['nullable','numeric','digits:10',Rule::unique('empleados')->ignore($this->route("empleado")->id)],
            "curp" => ['nullable', 'string', 'min:18', 'max:18', Rule::unique('empleados')->ignore($this->route("empleado")->id)],
            "rfc" => ['nullable', 'string', 'min:13', 'max:13', Rule::unique('empleados')->ignore($this->route("empleado")->id)],
            "ine" => ['nullable', 'numeric', 'digits:10', Rule::unique('empleados')->ignore($this->route("empleado")->id)],
            "licencia_de_manejo" => ['nullable', 'string', 'max:255', Rule::unique('empleados')->ignore($this->route("empleado")->id)],
            "nss" => ['nullable', 'numeric', 'digits:11', Rule::unique('empleados')->ignore($this->route("empleado")->id)],
            "fecha_de_ingreso" => ['required', 'date'],
            "hijos" => ['nullable', 'integer', 'max:99'],
            "dependientes_economicos" => ['nullable', 'integer', 'max:99'],
            "cedula_profesional" => ['nullable', 'string', 'min:7', 'max:15', Rule::unique('empleados')->ignore($this->route("empleado")->id)],
            "matriz" => ['boolean'],
            "sueldo_base" => ['nullable', 'integer'],
            "comision" => ['boolean'],
            "numero_exterior"=>['nullable','string'],
            "numero_interior"=>['nullable','string'],
            "calle" => ['nullable','string','max:255'],
            "colonia" => ['nullable','string','max:255'],
            "codigo_postal"=>['nullable','numeric','digits:5'],
            "ciudad" => ['nullable','string','max:255'],
            "estado" => ['nullable','string','max:255'],

            "cuenta_bancaria" => ['nullable','string','min:18','max:18'],
            "constelacion_familiar"=>['nullable','string','max:255'],
            "status"=>['nullable','string','max:255'],
            'correo_institucional' => ['nullable','email',Rule::unique('empleados')->ignore($this->route("empleado")->id)],

            "user_id" => ['nullable', 'integer', Rule::unique('empleados')->ignore($this->route("empleado")->id)],
            "escolaridad_id"=>['nullable','integer'],
            "puesto_id" => ['required', 'integer'],
            "sucursal_id" => ['required', 'integer'],
            "linea_id" => ['required', 'integer'],
            "departamento_id" => ['required', 'integer'],
            "estado_civil_id" => ['nullable', 'integer'],
            "tipo_de_sangre_id" => ['nullable', 'integer'],
            "expediente_id" => ['nullable', 'integer', Rule::unique('empleados')->ignore($this->route("empleado")->id)],
            "desvinculacion_id" => ['nullable', 'integer', Rule::unique('empleados')->ignore($this->route("empleado")->id)],
            "jefe_directo_id" => ['nullable', 'integer'],

            'constelacion_id'=>['nullable','array'],
            'alergias_id'=>['nullable','array'],
            'enfermedad_id'=>['nullable','array'],

            "descripcion_puesto" => ['nullable','string','max:255'],
            "carrera" => ['nullable','string','max:255'],
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
