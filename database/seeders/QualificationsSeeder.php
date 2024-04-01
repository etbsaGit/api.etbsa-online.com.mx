<?php

namespace Database\Seeders;

use App\Models\Qualification;
use App\Models\LineaTechnician;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class QualificationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Qualification::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        LineaTechnician::all()->each(function ($lineaTechnician) {
            // Iterar tres veces para agregar tres entradas por cada LineaTechnician
            for ($i = 1; $i <= 3; $i++) {
                // Construir el nombre de la calificación
                $qualificationName = "Requisito $i del técnico {$lineaTechnician->technician->name} de la línea {$lineaTechnician->linea->nombre}";

                // Crear la calificación
                Qualification::create([
                    'name' => $qualificationName,
                    'linea_technician_id' => $lineaTechnician->id,
                ]);
            }
        });
    }
}
