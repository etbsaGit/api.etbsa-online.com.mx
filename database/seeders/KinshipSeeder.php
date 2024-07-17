<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Intranet\Kinship;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class KinshipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Kinship::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = [
            ['id' => 1, 'name' => 'Jefe'],
            ['id' => 2, 'name' => 'Jefa'],
            ['id' => 3, 'name' => 'Padre'],
            ['id' => 4, 'name' => 'Madre'],
            ['id' => 5, 'name' => 'Esposa'],
            ['id' => 6, 'name' => 'Esposo'],
            ['id' => 7, 'name' => 'Hijo'],
            ['id' => 8, 'name' => 'Hija'],
            ['id' => 9, 'name' => 'Amigo'],
            ['id' => 10, 'name' => 'Vecino'],
            ['id' => 11, 'name' => 'Conocido'],
            ['id' => 12, 'name' => 'Proveedor'],
            ['id' => 13, 'name' => 'Otro']
        ];

        foreach ($data as $item) {
            Kinship::create(['name' => $item['name']]);
        }

    }
}
