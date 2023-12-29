<?php

namespace Database\Seeders;

use App\Models\Escolaridad;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class EscolaridadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Escolaridad::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $escolaridades = [
           'Primaria',
           'Secundaria',
           'Bachillerato',
           'Licenciatura',
           'Maestria',
           'Doctorado'
        ];

        foreach ($escolaridades as $escolaridad) {
            Escolaridad::create(['nombre' => $escolaridad]);
        }
    }
}
