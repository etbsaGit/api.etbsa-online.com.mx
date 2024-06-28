<?php

namespace Database\Seeders;

use App\Models\Bay;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class BaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Bay::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        for ($i = 1; $i <= 12; $i++) {
            Bay::create([
                'nombre' => (string) $i,  // Convertir a cadena para asegurar tipo de dato
                'estatus_id' => 40,
                'sucursal_id' => 1,
                'linea_id' => 1,
            ]);
        }

        for ($i = 1; $i <= 7; $i++) {
            Bay::create([
                'nombre' => (string) $i,  // Convertir a cadena para asegurar tipo de dato
                'estatus_id' => 40,
                'sucursal_id' => 1,
                'linea_id' => 2,
            ]);
        }
    }
}
