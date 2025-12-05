<?php

namespace App\Http\Requests\SoftSkillEmpleado;

use Illuminate\Foundation\Http\FormRequest;

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
        if ($this->has('skills')) {
            $skills = collect($this->skills)->map(function ($skill) {
                $skill['definicion'] = $skill['definicion'] === '' ? null : $skill['definicion'];
                $skill['evidencia']  = $skill['evidencia'] === '' ? null : $skill['evidencia'];
                $skill['soft_skill_nivel_id'] = $skill['soft_skill_nivel_id'] ?? null;
                return $skill;
            });

            $this->merge([
                'skills' => $skills->toArray(),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'skills' => ['required', 'array', 'min:1'],

            // ✅ NECESARIO solo para poder actualizar
            'skills.*.id' => ['required', 'integer'],

            // ✅ SOLO ESTOS 3 CAMPOS SE VALIDAN
            'skills.*.definicion' => ['nullable', 'string', 'max:1000'],

            'skills.*.evidencia' => ['nullable', 'string', 'max:1000'],

            'skills.*.soft_skill_nivel_id' => [
                'nullable',
                'integer',
                'exists:soft_skill_niveles,id'
            ],
        ];
    }
}
