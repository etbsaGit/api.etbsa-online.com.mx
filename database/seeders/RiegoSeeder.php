<?php

namespace Database\Seeders;

use App\Models\Intranet\Riego;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RiegoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Riego::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = [
            'Gravedad o Rodado (HAS)',
            'Aspersión (HAS)',
            'Goteo (HAS)',
            'Otros Bombeos (HAS)',
            'Goteo y Acolchado',
            'Otro Compuertas',
            'Pozo',
        ];

        foreach ($data as $item) {
            Riego::create(['name' => $item]);
        }
    }
}
