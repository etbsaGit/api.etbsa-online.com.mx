<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Intranet\Segmentation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SegmentationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Segmentation::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = [
            ['id' => 1, 'name' => 'Chico'],
            ['id' => 2, 'name' => 'Mediano'],
            ['id' => 3, 'name' => 'Grande'],
            ['id' => 4, 'name' => 'Agroindustrial'],
            ['id' => 5, 'name' => 'Jardinero'],
        ];

        foreach ($data as $item) {
            Segmentation::create(['name' => $item['name']]);
        }

    }
}
