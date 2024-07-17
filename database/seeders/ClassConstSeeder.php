<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Intranet\ConstructionClassification;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ClassConstSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        ConstructionClassification::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = [
            ['id' => 1, 'name' => 'Inmobiliario'],
            ['id' => 2, 'name' => 'Renta'],
            ['id' => 3, 'name' => 'Gubernamental'],
            ['id' => 4, 'name' => 'Ganadero'],
            ['id' => 5, 'name' => 'Industrial'],
        ];

        foreach ($data as $item) {
            ConstructionClassification::create(['name' => $item['name']]);
        }
    }
}
