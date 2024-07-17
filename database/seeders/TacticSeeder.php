<?php

namespace Database\Seeders;

use App\Models\Intranet\Tactic;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TacticSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Tactic::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = [
            ['id' => 1, 'name' => 'Xperimentar'],
            ['id' => 2, 'name' => 'Conociendo el Territorio'],
            ['id' => 3, 'name' => 'Liderazgo Sostenido'],
            ['id' => 4, 'name' => 'Embajadores'],
        ];

        foreach ($data as $item) {
            Tactic::create(['name' => $item['name']]);
        }

    }
}
