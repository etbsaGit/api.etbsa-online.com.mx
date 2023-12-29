<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoDeAsignacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TipoDeAsignacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        TipoDeAsignacion::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $tiposDeAsignaciones = [
          'Zapato',
          'Playera',
          'Pantalon',
          'Faja',
          'Lentes',
          'Mandil',
          'Guantes',
          'Overol',
          'Vehiculo',
          'Celular',
          'Alquiler de vivienda',
          'Computadora',
          'Impresora',
        ];

        foreach ($tiposDeAsignaciones as $tipoDeAsignacion) {
            TipoDeAsignacion::create(['nombre' => $tipoDeAsignacion]);
        }
    }
}
