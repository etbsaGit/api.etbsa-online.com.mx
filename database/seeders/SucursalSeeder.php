<?php

namespace Database\Seeders;

use App\Models\Sucursal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SucursalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Sucursal::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        Sucursal::create([
            'nombre'=>'Celaya',
            'direccion'=>'Carretera Celaya-Salamanca Km. 61 Primera Frac. de Crespo 38120'
        ]);

        Sucursal::create([
            'nombre'=>'Queretaro 5 de febrero',
            'direccion'=>'Av 5 de Febrero 719 76010'
        ]);

        Sucursal::create([
            'nombre'=>'Salamanca',
            'direccion'=>'Carretera Salamanca-Valle Km. 3.5 36780'
        ]);

        Sucursal::create([
            'nombre'=>'Silao',
            'direccion'=>'Carretera Silao-Leon Km. 15 36275'
        ]);

        Sucursal::create([
            'nombre'=>'Irapuato',
            'direccion'=>'Ejido Irapuato 1402 Frente cemento cruz azul Rancho Grande 36826'
        ]);

        Sucursal::create([
            'nombre'=>'Abasolo',
            'direccion'=>'Carretera Abasolo- Irapuato Km 6 36500'
        ]);

        Sucursal::create([
            'nombre'=>'Queretato el colorado',
            'direccion'=>'Autopista México - Querétaro Km 194 + 900 El Colorado, El Marqués, Querétaro 76246'
        ]);

        Sucursal::create([
            'nombre'=>'San Luis de la Paz',
            'direccion'=>'Carretera Federal Libre 57 Querétaro-San Luis de la Paz Km 308. Localidad Crucero San Luis de la Paz 37900'
        ]);

        Sucursal::create([
            'nombre'=>'Acambaro',
            'direccion'=>'Carretera Acámbaro-Jerécuaro Km 1, col. Loma Bonita 38610'
        ]);

        Sucursal::create([
            'nombre'=>'Morelia',
        ]);

        Sucursal::create([
            'nombre'=>'El Maguey',
        ]);

        Sucursal::create([
            'nombre'=>'Nuevas Tecnologias',
        ]);

        Sucursal::create([
            'nombre'=>'Matriz',
        ]);
        
    }

}
