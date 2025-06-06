<?php

namespace App\Http\Requests\Empleado;

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

    protected function prepareForValidation()
    {
        // Si el estatus_id no es 6, elimina la clave 'desvinculacion'
        if ($this->input('estatus_id') !== 6) {
            $this->merge($this->except('desvinculacion'));
        }
    }



    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            "base64" => ['nullable', 'string'],
            "nombre" => ['required', 'string', 'max:255'],
            "segundo_nombre" => ['nullable', 'string', 'max:255'],
            "apellido_paterno" => ['required', 'string', 'max:255'],
            "apellido_materno" => ['required', 'string', 'max:255'],
            "fecha_de_nacimiento" => ['nullable', 'date'],
            "telefono" => ['nullable', 'numeric', 'digits:10', 'unique:empleados,telefono'],
            "telefono_institucional" => ['nullable', 'numeric', 'digits:10', 'unique:empleados,telefono_institucional'],
            "curp" => ['nullable', 'string', 'min:18', 'max:18', 'unique:empleados,curp'],
            "rfc" => ['nullable', 'string', 'min:13', 'max:13', 'unique:empleados,rfc'],
            "ine" => ['nullable', 'numeric', 'digits:10', 'unique:empleados,ine'],
            "pasaporte" => ['nullable', 'string', 'mas:255', 'unique:empleados,pasaporte'],
            "visa" => ['nullable', 'numeric', 'digits:16', 'unique:empleados,visa'],
            "licencia_de_manejo" => ['nullable', 'string', 'max:255', 'unique:empleados,licencia_de_manejo'],
            "nss" => ['nullable', 'numeric', 'unique:empleados,nss'],
            "fecha_de_ingreso" => ['required', 'date'],
            "hijos" => ['nullable', 'integer', 'max:99'],
            "dependientes_economicos" => ['nullable', 'integer', 'max:99'],
            "cedula_profesional" => ['nullable', 'string', 'min:7', 'max:15', 'unique:empleados,cedulaProfesional'],
            "matriz" => ['boolean'],
            "sueldo_base" => ['nullable', 'integer'],
            "comision" => ['boolean'],
            "numero_exterior" => ['nullable', 'string'],
            "numero_interior" => ['nullable', 'string'],
            "calle" => ['nullable', 'string', 'max:255'],
            "colonia" => ['nullable', 'string', 'max:255'],
            "codigo_postal" => ['nullable', 'numeric', 'digits:5'],
            "ciudad" => ['nullable', 'string', 'max:255'],
            "estado" => ['nullable', 'string', 'max:255'],
            "cuenta_bancaria" => ['nullable', 'string', 'min:18', 'max:18'],
            'correo_institucional' => ['nullable', 'email', 'unique:empleados,correo_institucional'],
            "user_id" => ['nullable', 'integer', 'unique:empleados,user_id'],
            "escolaridad_id" => ['nullable', 'integer'],
            "puesto_id" => ['required', 'integer'],
            "sucursal_id" => ['required', 'integer'],
            "linea_id" => ['required', 'integer'],
            "departamento_id" => ['required', 'integer'],
            "estado_civil_id" => ['nullable', 'integer'],
            "tipo_de_sangre_id" => ['nullable', 'integer'],
            "expediente_id" => ['nullable', 'integer', 'unique:empleados,expediente_id'],
            "jefe_directo_id" => ['nullable', 'integer'],
            "notificar_id" => ['nullable', 'integer'],
            "estatus_id" => ['required', 'integer'],
            "descripcion_puesto" => ['nullable', 'string', 'max:255'],
            "carrera" => ['nullable', 'string', 'max:255'],
        ];

        $this->addDesvinculacionRules($rules);

        return $rules;
    }

    protected function addDesvinculacionRules(array &$rules)
    {
        if ($this->input('estatus_id') === 6) {
            $rules['desvinculacion'] = ['nullable', 'array'];
            $rules['desvinculacion.reason_id'] = ['required_with:desvinculacion', 'integer', 'exists:estatus,id'];
            $rules['desvinculacion.estatus_id'] = ['required_with:desvinculacion', 'integer', 'exists:estatus,id'];
            $rules['desvinculacion.date'] = ['required_with:desvinculacion', 'date'];
            $rules['desvinculacion.comments'] = ['nullable', 'string'];
        }
    }


    function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            $response = new Response($validator->errors(), 422);
            throw new ValidationException($validator, $response);
        }
    }
}
