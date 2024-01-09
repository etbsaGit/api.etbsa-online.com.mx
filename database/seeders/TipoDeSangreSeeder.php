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
            'nombre' => 'A+',
            'puedeDonar' => 'A+, AB+',
            'puedeRecibir' => 'A+, A-, O+, O-',
        ]);

        TipoDeSangre::create([
            'nombre' => 'O+',
            'puedeDonar' => 'O+, A+, B+, AB+',
            'puedeRecibir' => 'O+, O-',
        ]);

        TipoDeSangre::create([
            'nombre' => 'B+',
            'puedeDonar' => 'B+, AB+',
            'puedeRecibir' => 'B+, B-, O+, O-',
        ]);

        TipoDeSangre::create([
            'nombre' => 'AB+',
            'puedeDonar' => 'AB+',
            'puedeRecibir' => 'Todos',
        ]);

        TipoDeSangre::create([
            'nombre' => 'A-',
            'puedeDonar' => 'A+, A- AB+, AB-',
            'puedeRecibir' => 'A-, O-',
        ]);

        TipoDeSangre::create([
            'nombre' => 'O-',
            'puedeDonar' => 'Todos',
            'puedeRecibir' => 'O-',
        ]);

        TipoDeSangre::create([
            'nombre' => 'B-',
            'puedeDonar' => 'B+, B-, AB+, AB-',
            'puedeRecibir' => 'B-, O-',
        ]);

        TipoDeSangre::create([
            'nombre' => 'AB-',
            'puedeDonar' => 'AB+, AB-',
            'puedeRecibir' => 'AB-, A-, B-, O-',
        ]);
}
}