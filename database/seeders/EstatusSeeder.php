<?php

namespace Database\Seeders;

use App\Models\Estatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class EstatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Estatus::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $estatuses = [
           'pendiente',
           'enviado',
           'rechazado',
           'validado',
        ];

        foreach ($estatuses as $estatus) {
            Estatus::create(['nombre' => $estatus]);
        }
    }
}
