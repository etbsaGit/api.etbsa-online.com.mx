<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Intranet\NuevaTecnologia;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class NuevaTecnologiaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        NuevaTecnologia::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = [
            'JDLINK',
            'AMS',
            'DRON',
            'SISTEMA SATELITAL',
            'GPS',
            'HECTAREAS CONECTADAS',
            'OPERATION CENTER',
        ];

        foreach ($data as $item) {
            NuevaTecnologia::create(['name' => $item]);
        }
    }
}
