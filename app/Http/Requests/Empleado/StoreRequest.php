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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "nombre" => ['required', 'string', 'max:255'],
            "segundoNombre" => ['nullable', 'string', 'max:255'],
            "apellidoPaterno" => ['required', 'string', 'max:255'],
            "apellidoMaterno" => ['required', 'string', 'max:255'],
            "fechaDeNacimiento" => ['required', 'date'],
            "telefono"=>['required','numeric','digits:10','unique:referencias_personales,telefono'],
            "curp" => ['required', 'string', 'min:18', 'max:18', 'unique:empleados,curp'],
            "rfc" => ['required', 'string', 'min:13', 'max:13', 'unique:empleados,rfc'],
            "ine" => ['required', 'string', 'min:18', 'max:18', 'unique:empleados,ine'],
            "pasaporte" => ['nullable', 'string', 'mas:255', 'unique:empleados,pasaporte'],
            "visa" => ['nullable', 'numeric', 'digits:16', 'unique:empleados,visa'],
            "licenciaDeManejo" => ['nullable', 'string', 'min:12', 'max:18', 'unique:empleados,licenciaDeManejo'],
            "nss" => ['required', 'numeric', 'digits:11', 'unique:empleados,nss'],
            "fechaDeIngreso" => ['required', 'date'],
            "hijos" => ['nullable', 'integer','max:99'],
            "dependientesEconomicos" => ['nullable', 'integer','max:99'],
            "cedulaProfesional" => ['nullable', 'string', 'min:7', 'max:15', 'unique:empleados,cedulaProfesional'],
            "matriz" => ['boolean'],
            "sueldoBase"=>['required','integer'],
            "comision"=>['boolean'],
            "foto"=>['nullable'],
            "numeroExterior"=>['required','integer'],
            "numeroInterior"=>['nullable','string'],
            "calle" => ['required','string','max:255'],
            "colonia" => ['required','string','max:255'],
            "codigoPostal"=>['required','numeric','digits:5'],
            "ciudad" => ['required','string','max:255'],
            "estado" => ['required','string','max:255'],
            "cuentaBancaria" => ['required','string','min:18','max:18'],
            "constelacionFamiliar"=>['nullable','string','max:255'],

            "user_id"=>['nullable','integer','unique:empleados,user_id'],
            "escolaridad_id"=>['nullable','integer'],
            "puesto_id"=>['required','integer'],
            "sucursal_id"=>['required','integer'],
            "linea_id"=>['required','integer'],
            "departamento_id"=>['required','integer'],
            "estadoCivil_id"=>['required','integer'],
            "tipoDeSangre_id"=>['required','integer'],
            "expediente_id"=>['nullable','integer','unique:empleados,expediente_id'],
            "desvinculacion_id"=>['nullable','integer','unique:empleados,direccion_id'],
            "jefeDirecto"=>['nullable','integer'],
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
