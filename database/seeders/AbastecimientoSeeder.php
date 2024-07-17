<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Intranet\Abastecimiento;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AbastecimientoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Abastecimiento::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = [
            'Precipitación Promedio (mm)',
            'Pozo Profundo (LPS)',
            'Superfiecie (LPS)',
            'Otros (LPS)',
            'Pozo Profundo (HAS)',
            'Superficial (HAS)',
            'Otros (HAS)',
        ];

        foreach ($data as $item) {
            Abastecimiento::create(['name' => $item]);
        }
    }
}
