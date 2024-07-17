<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Intranet\ClasEquipo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ClasEquipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        ClasEquipo::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = [
            'Maquinaria Construcción',
            'Maquinaria Agricola',
            'Implemento Agricola',
            'Vehiculo Utilitario',
            'Vehiculo de Carga',
            'Trasporte Utilitario',
            'Transporte Carga',
        ];

        foreach ($data as $item) {
            ClasEquipo::create(['name' => $item]);
        }
    }
}
