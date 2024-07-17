<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Intranet\TechnologicalCapability;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TechnologicalCapabilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        TechnologicalCapability::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = [
            ['id' => 1, 'name' => 'Baja'],
            ['id' => 2, 'name' => 'Mediana'],
            ['id' => 3, 'name' => 'Alta'],
            ['id' => 4, 'name' => 'Experto'],
        ];

        foreach ($data as $item) {
            TechnologicalCapability::create(['name' => $item['name']]);
        }
    }

}
