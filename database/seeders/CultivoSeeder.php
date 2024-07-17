<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Intranet\Cultivo;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CultivoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Cultivo::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = [
            'Aguacate',
            'Ajo',
            'Alfalfa',
            'Apio',
            'Avena',
            'Brocoli',
            'Calabaza',
            'Cebada',
            'Cebolla',
            'Chile',
            'Cilantro',
            'Col',
            'Coliflor',
            'Esparrago',
            'Espinaca',
            'Fresa',
            'Frijol',
            'Hortalizas',
            'Jicama',
            'Jitomate',
            'Lechuga',
            'Maiz',
            'Papa',
            'Sorgo',
            'Tomate',
            'Trigo',
            'Zanahoria',
            'Zarzamora',
        ];

        foreach ($data as $item) {
            Cultivo::create(['name' => $item]);
        }
    }
}
