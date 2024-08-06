<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Intranet\Condicion;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CondicionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Condicion::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = [
            'Nuevo',
            'Seminuevo',
            'En buen estado',
            'Regular',
            'Mal Estado',
            'No Funciona',
            'Obsoleto',
        ];

        foreach ($data as $item) {
            Condicion::create(['name' => $item]);
        }
    }
}
