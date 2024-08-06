<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Intranet\TipoCultivo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TipoCultivoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        TipoCultivo::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = [
            'Cultivos Alimentarios',
            'Cultivos para forraje',
            'Cultivos Textiles',
            'Cultivos Oleaginosos',
            'Cultivos Ornamentales',
            'Cultivos Industriales',
        ];

        foreach ($data as $item) {
            TipoCultivo::create(['name' => $item]);
        }
    }
}
