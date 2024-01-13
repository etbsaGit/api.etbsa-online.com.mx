<?php

namespace Database\Seeders;

use App\Models\Linea;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LineaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Linea::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $lineas = [
            'Agricola',
            'Construccion',
            'Combo'
        ];

        foreach ($lineas as $linea) {
            Linea::create(['nombre' => $linea]);
        }
    }
}
