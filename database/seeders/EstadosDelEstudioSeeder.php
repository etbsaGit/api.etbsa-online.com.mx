<?php

namespace Database\Seeders;

use App\Models\EstadoDeEstudio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class EstadosDelEstudioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        EstadoDeEstudio::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $estadosDeEstudio = [
            'Finalizado',
            'En proceso',
            'Trunco'
        ];

        foreach ($estadosDeEstudio as $estadoDeEstudio) {
            EstadoDeEstudio::create(['nombre' => $estadoDeEstudio]);
        }
    }
}
