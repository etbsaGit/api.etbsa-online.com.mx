<?php

namespace Database\Seeders;

use App\Models\Estatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class EstatusDelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Estatus::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        // Poblar la tabla 'estatus' con datos de prueba
        Estatus::create([
            'nombre' => 'Aceptado',
            'clave' => 'aceptado',
            'tipo_estatus' => 'archivo',
            'color' => 'green' // Color verde
        ]);

        Estatus::create([
            'nombre' => 'Rechazado',
            'clave' => 'rechazado',
            'tipo_estatus' => 'archivo',
            'color' => 'red' // Color rojo
        ]);

        Estatus::create([
            'nombre' => 'Pendiente',
            'clave' => 'pendiente',
            'tipo_estatus' => 'archivo',
            'color' => 'orange' // Color naranja
        ]);

        Estatus::create([
            'nombre' => 'Enviado',
            'clave' => 'enviado',
            'tipo_estatus' => 'archivo',
            'color' => 'blue' // Color azul
        ]);

        Estatus::create([
            'nombre' => 'Activo',
            'clave' => 'activo',
            'tipo_estatus' => 'empleado',
            'color' => 'green' // Color verde
        ]);

        Estatus::create([
            'nombre' => 'Baja',
            'clave' => 'baja',
            'tipo_estatus' => 'empleado',
            'color' => 'red' // Color rojo
        ]);

        Estatus::create([
            'nombre' => 'Pensionado',
            'clave' => 'pensionado',
            'tipo_estatus' => 'empleado',
            'color' => 'gray' // Color gris
        ]);

        Estatus::create([
            'nombre' => 'Suspendido',
            'clave' => 'suspendido',
            'tipo_estatus' => 'empleado',
            'color' => 'orange' // Color naranja
        ]);

        Estatus::create([
            'nombre' => 'Renuncia',
            'clave' => 'renuncia',
            'tipo_estatus' => 'termination',
            'color' => 'yellow' // Color amarillo
        ]);

        Estatus::create([
            'nombre' => 'Despido',
            'clave' => 'despido',
            'tipo_estatus' => 'termination',
            'color' => 'brown' // Color marrón
        ]);

        // Nuevos estados bajo 'terminationType'
        Estatus::create([
            'nombre' => 'Otra oferta económica',
            'clave' => 'otra_oferta_economica',
            'tipo_estatus' => 'terminationType',
            'color' => 'purple' // Color púrpura
        ]);

        Estatus::create([
            'nombre' => 'Motivos familiares',
            'clave' => 'motivos_familiares',
            'tipo_estatus' => 'terminationType',
            'color' => 'pink' // Color rosa
        ]);

        Estatus::create([
            'nombre' => 'Motivos personales',
            'clave' => 'motivos_personales',
            'tipo_estatus' => 'terminationType',
            'color' => 'lightblue' // Color azul claro
        ]);

        Estatus::create([
            'nombre' => 'Relación con mis compañeros de trabajo',
            'clave' => 'relacion_companeros_trabajo',
            'tipo_estatus' => 'terminationType',
            'color' => 'cyan' // Color cian
        ]);

        Estatus::create([
            'nombre' => 'Relación con mi jefe',
            'clave' => 'relacion_jefe',
            'tipo_estatus' => 'terminationType',
            'color' => 'lightgreen' // Color verde claro
        ]);

        Estatus::create([
            'nombre' => 'Desmotivación del puesto',
            'clave' => 'desmotivacion_puesto',
            'tipo_estatus' => 'terminationType',
            'color' => 'brown' // Color marrón
        ]);

        Estatus::create([
            'nombre' => 'Desmotivación por realizar tareas para las cuales estoy calificado',
            'clave' => 'desmotivacion_tareas_calificado',
            'tipo_estatus' => 'terminationType',
            'color' => 'darkblue' // Color azul oscuro
        ]);

        Estatus::create([
            'nombre' => 'Mejor horario laboral',
            'clave' => 'mejor_horario_laboral',
            'tipo_estatus' => 'terminationType',
            'color' => 'orange' // Color naranja
        ]);

        Estatus::create([
            'nombre' => 'Continuar estudios',
            'clave' => 'continuar_estudios',
            'tipo_estatus' => 'terminationType',
            'color' => 'lightpurple' // Color morado claro
        ]);

        Estatus::create([
            'nombre' => 'Fraude',
            'clave' => 'fraude',
            'tipo_estatus' => 'terminationType',
            'color' => 'black' // Color negro
        ]);

        Estatus::create([
            'nombre' => 'Robo',
            'clave' => 'robo',
            'tipo_estatus' => 'terminationType',
            'color' => 'darkred' // Color rojo oscuro
        ]);

        Estatus::create([
            'nombre' => 'Bajo desempeño',
            'clave' => 'bajo_desempeno',
            'tipo_estatus' => 'terminationType',
            'color' => 'darkgray' // Color gris oscuro
        ]);

        Estatus::create([
            'nombre' => 'Ausentismo',
            'clave' => 'ausentismo',
            'tipo_estatus' => 'terminationType',
            'color' => 'darkorange' // Color naranja oscuro
        ]);

        Estatus::create([
            'nombre' => 'Abandono de trabajo',
            'clave' => 'abandono_trabajo',
            'tipo_estatus' => 'terminationType',
            'color' => 'darkyellow' // Color amarillo oscuro
        ]);

        Estatus::create([
            'nombre' => 'Jubilación',
            'clave' => 'jubilacion',
            'tipo_estatus' => 'terminationType',
            'color' => 'lightgray' // Color gris claro
        ]);

        Estatus::create([
            'nombre' => 'Otro',
            'clave' => 'otro',
            'tipo_estatus' => 'terminationType',
            'color' => 'lightbrown' // Color marrón claro
        ]);

        // Puedes agregar más registros según sea necesario
    }
}
