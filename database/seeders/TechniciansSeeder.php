<?php

namespace Database\Seeders;

use App\Models\Linea;
use App\Models\Technician;
use App\Models\LineaTechnician;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TechniciansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Technician::truncate();
        LineaTechnician::truncate(); // Limpiar la tabla pivote LineaTechnician
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = [
            ['name' => 'Ayudante', 'level' => 1],
            ['name' => 'C', 'level' => 2],
            ['name' => 'B', 'level' => 3],
            ['name' => 'A', 'level' => 4],
            ['name' => 'AA', 'level' => 5],
            ['name' => 'AAA', 'level' => 6],
        ];

        foreach ($data as $technicianData) {
            $technician = Technician::create($technicianData);

            // Crear LineaTechnician para la línea "Agricola"
            $lineaAgricola = Linea::where('nombre', 'Agricola')->first();
            $lineaTechnicianAgricola = new LineaTechnician();
            $lineaTechnicianAgricola->linea()->associate($lineaAgricola);
            $lineaTechnicianAgricola->technician()->associate($technician);
            $lineaTechnicianAgricola->save();

            // Crear LineaTechnician para la línea "Construccion"
            $lineaConstruccion = Linea::where('nombre', 'Construccion')->first();
            $lineaTechnicianConstruccion = new LineaTechnician();
            $lineaTechnicianConstruccion->linea()->associate($lineaConstruccion);
            $lineaTechnicianConstruccion->technician()->associate($technician);
            $lineaTechnicianConstruccion->save();
        }
    }
}
