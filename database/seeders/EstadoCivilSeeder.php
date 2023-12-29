<?php

namespace Database\Seeders;

use App\Models\EstadoCivil;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class EstadoCivilSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        EstadoCivil::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $estadosCiviles = [
            'Soltero(a)',
            'Casado(a)',
            'Viudo(a)',
            'Divorciado(a)',
            'Separado(a)',
            'Union Libre'
        ];

        foreach ($estadosCiviles as $estadoCivil) {
            EstadoCivil::create(['nombre' => $estadoCivil]);
        }
    }
}
