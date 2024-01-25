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

       Estatus::create([
        'nombre' => 'Aceptado',
        'clave' => 'aceptado',
        'tipo_estatus' => 'archivo',
        'color'=>'green'
       ]);

       Estatus::create([
        'nombre' => 'Rechazado',
        'clave' => 'rechazado',
        'tipo_estatus' => 'archivo',
        'color'=>'red'
       ]);

       Estatus::create([
        'nombre' => 'Pendiente',
        'clave' => 'pendiente',
        'tipo_estatus' => 'archivo',
        'color'=>'yellow'
       ]);

       Estatus::create([
        'nombre' => 'Enviado',
        'clave' => 'enviado',
        'tipo_estatus' => 'archivo',
        'color'=>'blue'
       ]);
    }
}
