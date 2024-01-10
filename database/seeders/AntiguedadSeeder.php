<?php

namespace Database\Seeders;

use App\Models\Antiguedad;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AntiguedadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $diasInicial = 12;

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Antiguedad::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        for ($i = 1; $i <= 5; $i++){
            Antiguedad::create([
                'años_cumplidos'=>$i,
                'dias_correspondientes' => $diasInicial,
                //'regimen'=>2023
            ]);
            $diasInicial = $diasInicial+2;
        }

        for ($i = 6; $i <= 10; $i++){
            Antiguedad::create([
                'años_cumplidos'=>$i,
                'dias_correspondientes' => $diasInicial,
                //'regimen'=>2023
            ]);
        }


        for ($i = 11; $i <= 15; $i++){
            Antiguedad::create([
                'años_cumplidos'=>$i,
                'dias_correspondientes' => 24,
                //'regimen'=>2023
            ]);
        }

        for ($i = 16; $i <= 20; $i++){
            Antiguedad::create([
                'años_cumplidos'=>$i,
                'dias_correspondientes' => 26,
                //'regimen'=>2023
            ]);
        }

        for ($i = 21; $i <= 25; $i++){
            Antiguedad::create([
                'años_cumplidos'=>$i,
                'dias_correspondientes' => 28,
                //'regimen'=>2023
            ]);
        }

        for ($i = 26; $i <= 30; $i++){
            Antiguedad::create([
                'años_cumplidos'=>$i,
                'dias_correspondientes' => 30,
                //'regimen'=>2023
            ]);
        }

        for ($i = 31; $i <= 35; $i++){
            Antiguedad::create([
                'años_cumplidos'=>$i,
                'dias_correspondientes' => 32,
                //'regimen'=>2023
            ]);
        }

    }
}
