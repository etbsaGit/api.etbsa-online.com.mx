<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Intranet\TipoEquipo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TipoEquipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        TipoEquipo::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = [
            'ABONADORA',
            'ACAMADOR',
            'ADITAMENTO',
            'APISONADOR',
            'ARADO',
            'ASPERSORA',
            'AUTOBUS',
            'BARREDORA',
            'BARRENA',
            'CAMION',
            'CAMION / RABON',
            'CAMION ARTICULADO  / TORTON',
            'CAMIONETA',
            'CAMPERVAN',
            'CARGADOR',
            'CARGADOR FRONTAL',
            'COMPACTADOR',
            'CORTADORA',
            'COSECHADORA',
            'CULTIVADORA',
            'DESVARADORA',
            'DRON',
            'EMPACADORA',
            'ENSILADORA',
            'ESCREPA',
            'EXCAVADORA',
            'FERTILIZADORA',
            'FURGONETA',
            'HATCHBACK',
            'HOYADORA',
            'IMPLEMENTOS',
            'JARDINERO / GATOR',
            'JDLINK',
            'MARTILLO',
            'MINI TRUCK',
            'MINICARGADOR',
            'MINIEXCAVADORA',
            'MINIVAN',
            'MOLINO',
            'MOTONIVELADORA',
            'MULTICULTIVADOR',
            'PICKUP',
            'PODADORA',
            'PULVERIZADOR',
            'RASTRA',
            'RASTRILLO',
            'REMOLQUE',
            'RETROEXCAVADORA',
            'ROTOCULTIVADORA',
            'ROTURADOR',
            'SEGADORA',
            'SEMBRADORA',
            'SISTEMA SATELITAL',
            'SUBSUELO',
            'SUV',
            'TRACTO CAMION',
            'TRACTO CAMION DOBLEMENTE ARTICULADO',
            'TRACTOR',
            'TRAILER',
            'USADO AGRICOLA',
            'VAN',
            'VEHICULO SEDAN',
            'VEHICULO UTILITARIO',
        ];

        foreach ($data as $item) {
            TipoEquipo::create(['name' => $item]);
        }
    }
}
