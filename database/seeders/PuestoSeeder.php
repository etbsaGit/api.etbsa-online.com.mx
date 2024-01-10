<?php

namespace Database\Seeders;

use App\Models\Puesto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PuestoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Puesto::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $puestos = [
            'Almacenista',
            'Analista de incentivos',
            'Armador',
            'Asesor',
            'Asistente de direccion',
            'Auditor interno',
            'Auxiliar',
            'Ayudante',
            'Cajera',
            'CDI (Capacitador interno del distribuidor)',
            'Chofer',
            'Consultor',
            'Contador',
            'Controlista',
            'Coordinador',
            'Director',
            'Diseñador Grafico',
            'Especialista',
            'Generalista',
            'Gerente',
            'Gestor',
            'Instalador',
            'Intendente',
            'Jefe',
            'Vendedor',
            'Vigilante',
            'Supervisor',
            'Programador'
        ];

        foreach ($puestos as $puesto) {
            Puesto::create(['nombre' => $puesto]);
        }
    }
}
