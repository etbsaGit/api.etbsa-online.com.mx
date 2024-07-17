<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Intranet\Classification;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ClassificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Classification::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = [
            ['id' => 1, 'name' => 'AAA'],
            ['id' => 2, 'name' => 'AA'],
            ['id' => 3, 'name' => 'A'],
            ['id' => 4, 'name' => 'Lista Negra'],
        ];

        foreach ($data as $item) {
            Classification::create(['name' => $item['name']]);
        }
    }
}
