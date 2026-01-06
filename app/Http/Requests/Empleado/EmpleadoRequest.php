<?php

namespace App\Http\Requests\Empleado;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class EmpleadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        // Casteo para evitar comparaciones raras ("6" vs 6)
        $estatusId = (int) $this->input('estatus_id');

        // Si no es 6, elimina desvinculacion del request
        if ($estatusId !== 6) {
            $this->request->remove('desvinculacion'); // mejor que merge(except())
        }
    }

    public function rules(): array
    {
        $item = $this->route('empleado');

        $rules = [
            "base64" => ['nullable', 'string'],
            "nombre" => ['required', 'string', 'max:255'],
            "segundo_nombre" => ['nullable', 'string', 'max:255'],
            "apellido_paterno" => ['required', 'string', 'max:255'],
            "apellido_materno" => ['required', 'string', 'max:255'],
            "fecha_de_nacimiento" => ['nullable', 'date'],

            "telefono" => ['nullable', 'numeric', 'digits:10', Rule::unique('empleados', 'telefono')->ignore($item?->id)],
            "telefono_institucional" => ['nullable', 'numeric', 'digits:10', Rule::unique('empleados', 'telefono_institucional')->ignore($item?->id)],
            "curp" => ['nullable', 'string', 'min:18', 'max:18', Rule::unique('empleados', 'curp')->ignore($item?->id)],
            "rfc" => ['nullable', 'string', 'min:13', 'max:13', Rule::unique('empleados', 'rfc')->ignore($item?->id)],
            "ine" => ['nullable', 'numeric', 'digits:10', Rule::unique('empleados', 'ine')->ignore($item?->id)],
            "pasaporte" => ['nullable', 'string', 'max:255', Rule::unique('empleados', 'pasaporte')->ignore($item?->id)],
            "visa" => ['nullable', 'numeric', 'digits:16', Rule::unique('empleados', 'visa')->ignore($item?->id)],
            "licencia_de_manejo" => ['nullable', 'string', 'max:255', Rule::unique('empleados', 'licencia_de_manejo')->ignore($item?->id)],
            "nss" => ['nullable', 'numeric', Rule::unique('empleados', 'nss')->ignore($item?->id)],

            "fecha_de_ingreso" => ['required', 'date'],
            "hijos" => ['nullable', 'integer', 'max:99'],
            "dependientes_economicos" => ['nullable', 'integer', 'max:99'],
            "cedula_profesional" => ['nullable', 'string', 'min:7', 'max:15', Rule::unique('empleados', 'cedulaProfesional')->ignore($item?->id)],

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
            "correo_institucional" => ['nullable', 'email', Rule::unique('empleados', 'correo_institucional')->ignore($item?->id)],

            "user_id" => ['nullable', 'integer', Rule::unique('empleados', 'user_id')->ignore($item?->id)],
            "escolaridad_id" => ['nullable', 'integer'],
            "puesto_id" => ['required', 'integer'],
            "sucursal_id" => ['required', 'integer'],
            "linea_id" => ['required', 'integer'],
            "departamento_id" => ['required', 'integer'],
            "estatus_id" => ['required', 'integer'],
            "descripcion_puesto" => ['nullable', 'string', 'max:255'],
            "carrera" => ['nullable', 'string', 'max:255'],
        ];

        $this->addDesvinculacionRules($rules);

        return $rules;
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres.',

            'apellido_paterno.required' => 'El apellido paterno es obligatorio.',
            'apellido_materno.required' => 'El apellido materno es obligatorio.',

            'telefono.digits' => 'El teléfono debe contener 10 dígitos.',
            'telefono.unique' => 'El teléfono ya está registrado.',

            'telefono_institucional.unique' => 'El teléfono institucional ya está registrado.',

            'curp.min' => 'La CURP debe contener 18 caracteres.',
            'curp.max' => 'La CURP debe contener 18 caracteres.',
            'curp.unique' => 'La CURP ya está registrada.',

            'rfc.min' => 'El RFC debe contener 13 caracteres.',
            'rfc.max' => 'El RFC debe contener 13 caracteres.',
            'rfc.unique' => 'El RFC ya está registrado.',

            'ine.unique' => 'El INE ya está registrado.',
            'visa.unique' => 'La visa ya está registrada.',

            'correo_institucional.email' => 'El correo institucional no es válido.',
            'correo_institucional.unique' => 'El correo institucional ya está registrado.',

            'puesto_id.required' => 'El puesto es obligatorio.',
            'sucursal_id.required' => 'La sucursal es obligatoria.',
            'linea_id.required' => 'La línea es obligatoria.',
            'departamento_id.required' => 'El departamento es obligatorio.',
            'estatus_id.required' => 'El estatus es obligatorio.',

            // Nested desvinculación
            'desvinculacion.reason_id.required_with' => 'El motivo de desvinculación es obligatorio.',
            'desvinculacion.reason_id.exists' => 'El motivo de desvinculación no existe.',
            'desvinculacion.estatus_id.required_with' => 'El estatus de desvinculación es obligatorio.',
            'desvinculacion.estatus_id.exists' => 'El estatus de desvinculación no existe.',
            'desvinculacion.date.required_with' => 'La fecha de desvinculación es obligatoria.',
            'desvinculacion.date.date' => 'La fecha de desvinculación no es válida.',

            // Cuando no debe enviarse desvinculación
            'desvinculacion' => 'No puedes enviar datos de desvinculación si el empleado no está desvinculado.',
        ];
    }

    protected function addDesvinculacionRules(array &$rules)
    {
        $estatusId = (int) $this->input('estatus_id');

        if ($estatusId === 6) {
            $rules['desvinculacion'] = ['nullable', 'array'];
            $rules['desvinculacion.reason_id'] = ['required_with:desvinculacion', 'integer', 'exists:estatus,id'];
            $rules['desvinculacion.estatus_id'] = ['required_with:desvinculacion', 'integer', 'exists:estatus,id'];
            $rules['desvinculacion.date'] = ['required_with:desvinculacion', 'date'];
            $rules['desvinculacion.comments'] = ['nullable', 'string'];
        }
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Errores de validación',
            'errors'  => $validator->errors()
        ], 422));
    }
}
