<?php

namespace Database\Seeders;

use App\Models\Constelacion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ConstelacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Constelacion::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $constelaciones = [
            'Mamá',
            'Papá',
            'Hijo',
            'Hija',
            'Abuelo Paterno',
            'Abuela Paterna',
            'Abuela Materna',
            'Abuelo Materno',
            'Tio(a)',
            'Primo(a)',
            'Esposo(a)',
            'Novio(a)',
        ];

        foreach ($constelaciones as $constelacion) {
            Constelacion::create(['nombre' => $constelacion]);
        }
    }
}
