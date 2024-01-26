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
            'puede_donar' => 'A+, AB+',
            'puede_recibir' => 'A+, A-, O+, O-',
        ]);

        TipoDeSangre::create([
            'nombre' => 'O+',
            'puede_donar' => 'O+, A+, B+, AB+',
            'puede_recibir' => 'O+, O-',
        ]);

        TipoDeSangre::create([
            'nombre' => 'B+',
            'puede_donar' => 'B+, AB+',
            'puede_recibir' => 'B+, B-, O+, O-',
        ]);

        TipoDeSangre::create([
            'nombre' => 'AB+',
            'puede_donar' => 'AB+',
            'puede_recibir' => 'Todos',
        ]);

        TipoDeSangre::create([
            'nombre' => 'A-',
            'puede_donar' => 'A+, A- AB+, AB-',
            'puede_recibir' => 'A-, O-',
        ]);

        TipoDeSangre::create([
            'nombre' => 'O-',
            'puede_donar' => 'Todos',
            'puede_recibir' => 'O-',
        ]);

        TipoDeSangre::create([
            'nombre' => 'B-',
            'puede_donar' => 'B+, B-, AB+, AB-',
            'puede_recibir' => 'B-, O-',
        ]);

        TipoDeSangre::create([
            'nombre' => 'AB-',
            'puede_donar' => 'AB+, AB-',
            'puede_recibir' => 'AB-, A-, B-, O-',
        ]);
}
}