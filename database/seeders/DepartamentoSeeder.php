<?php

namespace Database\Seeders;

use App\Models\Departamento;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DepartamentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Departamento::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $departamentos = [
            'Administraion',
            'Credito y Cobranza',
            'Direccion',
            'Lubricantes',
            'Marketing',
            'PostVenta',
            'Refaciones',
            'Rentas',
            'Recursos Humanos',
            'Riego',
            'Servicio',
            'Tecnologias de la informacion',
            'Taller',
            'Ventas',
            'Desarrollo de proyectos'
        ];

        foreach ($departamentos as $departamento) {
            Departamento::create(['nombre' => $departamento]);
        }

    }
}
