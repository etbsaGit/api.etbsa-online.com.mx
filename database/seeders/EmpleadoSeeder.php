<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Linea;
use App\Models\Puesto;
use App\Models\Empleado;
use App\Models\Sucursal;
use Illuminate\Support\Str;
use App\Models\Departamento;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmpleadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Empleado::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        Empleado::create([
            'nombre'=>'Cesar',
            'segundo_nombre'=>'Alberto',
            'apellido_paterno'=>'Aguayo',
            'apellido_materno'=>'Sanchez',
            'fecha_de_nacimiento'=>'1992/11/11',
            'telefono'=>'4616793987',
            'curp'=>'ausc92111123456789',
            'rfc'=>'ausc9211119w2',
            'ine'=>'123456789123456789',
            'licencia_de_manejo'=>'123456789qwerty',
            'nss'=>'12345678910',
            'fecha_de_ingreso'=>'2023/10/25',
            'matriz'=>true,
            'sueldo_base'=>14000,
            'comision'=>false,
            'numero_exterior'=>207,
            'calle'=>'marte',
            'colonia'=>'Zona de oro 1',
            'codigo_postal'=>38020,
            'ciudad'=>'Celaya',
            'estado'=>'guanajuato',
            'cuenta_bancaria'=>mt_rand(10**17, (10**18)-1),
            'user_id'=>1,
            'estado_civil_id'=>1,
            'tipo_de_sangre_id'=>1,
            'sucursal_id' => Sucursal::inRandomOrder()->first()->id,
            'linea_id' => Linea::inRandomOrder()->first()->id,
            'departamento_id' => Departamento::inRandomOrder()->first()->id,
            'puesto_id' => Puesto::inRandomOrder()->first()->id,
            'escolaridad_id'=>2,
            'status'=>'Activo'
        ]);

        // for ($i = 1; $i < 10; $i++) {
        //     Empleado::create(
        //         [
        //             'nombre'=>"Empleado $i",
        //             'segundoNombre'=>"nombre $i",
        //             'apellidoPaterno'=>"Apellido paterno $i",
        //             'apellidoMaterno'=>"Apellido Materno $i",
        //             'fechaDeNacimiento'=>Carbon::now()->subDays(rand(1, 30)),
        //             'curp'=>Str::random(18),
        //             'rfc'=>Str::random(13),
        //             'ine'=>'123456789123456789',
        //             'licenciaDeManejo'=>'123456789qwerty',
        //             'nss'=>'12345678910',
        //             'fechaDeIngreso'=>'2023/10/25',
        //             'matriz'=>true,
        //             'sueldoBase'=>14000,
        //             'comision'=>false,
        //             'telefono'=>'4616793987',
        //             'user_id'=>2+$i,
        //             'puesto_id'=>$i,
        //             'numeroExterior'=>207+$i,
        //             'calle'=>"calle $i",
        //             'colonia'=>"colonia $i",
        //             'codigoPostal'=>38020,
        //             'ciudad'=>"ciudad $i",
        //             'estado'=>"estado $i",
        //             'cuentaBancaria'=>mt_rand(10**17, (10**18)-1),
        //             'sucursal_id' => Sucursal::inRandomOrder()->first()->id,
        //             'linea_id' => Linea::inRandomOrder()->first()->id,
        //             'departamento_id' => Departamento::inRandomOrder()->first()->id,
        //             'puesto_id' => Puesto::inRandomOrder()->first()->id,

        //         ]
        //     );
        // }

    }
}
