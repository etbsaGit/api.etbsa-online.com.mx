<?php

namespace Database\Seeders;

use App\Models\TipoDeSangre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoDeSangreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        TipoDeSangre::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        TipoDeSangre::create([
            'nombre' => 'A',
            'puedeRecibir' => 'A, O',
            'puedeDonar' => 'A, AB',
        ]);

        TipoDeSangre::create([
            'nombre' => 'B',
            'puedeRecibir' => 'B, O',
            'puedeDonar' => 'B, AB',
        ]);

        TipoDeSangre::create([
            'nombre' => 'AB',
            'puedeRecibir' => 'A, B, AB, O',
            'puedeDonar' => 'AB',
        ]);

        TipoDeSangre::create([
            'nombre' => 'O',
            'puedeRecibir' => 'O',
            'puedeDonar' => 'A, B, AB, O',
        ]);
    }
}
