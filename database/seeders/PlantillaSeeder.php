<?php

namespace Database\Seeders;

use App\Models\Plantilla;
use App\Models\Requisito;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlantillaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        $plantilla = Plantilla::create([
            'nombre'=> 'Docs. Empleado'
        ]);

        $plantilla->requisito()->sync(Requisito::all()->pluck('id'));


    }
}
