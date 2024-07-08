<?php

namespace Database\Seeders;

use App\Models\Activity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActivitiesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Activity::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $activities = [
            [
                'id' => 1,
                'details' => 'Toma de fotografías',
                'completed' => 1,
                'event_id' => 1,
                'created_at' => '2024-06-07 20:43:19',
                'updated_at' => '2024-06-07 20:43:19',
                'deleted_at' => NULL,
            ],
            [
                'id' => 2,
                'details' => 'Toma de fotografía personal de agencia Irapuato',
                'completed' => 1,
                'event_id' => 2,
                'created_at' => '2024-06-12 21:47:36',
                'updated_at' => '2024-06-17 15:59:48',
                'deleted_at' => NULL,
            ],
            [
                'id' => 3,
                'details' => 'Atender entrevista candidato Juan José Fonseca (ventas Lubricantes Irapuato)',
                'completed' => 1,
                'event_id' => 2,
                'created_at' => '2024-06-12 21:47:36',
                'updated_at' => '2024-06-17 15:59:51',
                'deleted_at' => NULL,
            ],
            [
                'id' => 4,
                'details' => 'Atender entrevista candidato Carlos Alberto Córdoba (ventas Lubricantes Irapuato)',
                'completed' => 0,
                'event_id' => 2,
                'created_at' => '2024-06-12 21:47:36',
                'updated_at' => '2024-06-12 21:47:36',
                'deleted_at' => NULL,
            ],
            [
                'id' => 5,
                'details' => 'Toma de fotografías',
                'completed' => 1,
                'event_id' => 3,
                'created_at' => '2024-06-12 21:50:11',
                'updated_at' => '2024-06-17 16:00:00',
                'deleted_at' => NULL,
            ],
            [
                'id' => 6,
                'details' => 'Revisar ajuste de reloj checador',
                'completed' => 1,
                'event_id' => 3,
                'created_at' => '2024-06-12 21:50:11',
                'updated_at' => '2024-06-17 16:00:02',
                'deleted_at' => NULL,
            ],
            [
                'id' => 7,
                'details' => 'Toma de fotografías Qro. Colorado',
                'completed' => 1,
                'event_id' => 4,
                'created_at' => '2024-06-12 21:53:22',
                'updated_at' => '2024-06-14 21:08:46',
                'deleted_at' => NULL,
            ],
            [
                'id' => 8,
                'details' => 'Toma de fotografías 5 de Feb.',
                'completed' => 1,
                'event_id' => 4,
                'created_at' => '2024-06-12 21:53:22',
                'updated_at' => '2024-06-14 21:08:49',
                'deleted_at' => NULL,
            ],
            [
                'id' => 9,
                'details' => 'Atender entrevista candidato Tec. servicio Construcción',
                'completed' => 0,
                'event_id' => 4,
                'created_at' => '2024-06-12 21:53:22',
                'updated_at' => '2024-06-12 21:53:22',
                'deleted_at' => NULL,
            ],
            [
                'id' => 10,
                'details' => 'Toma de fotografías Silao',
                'completed' => 1,
                'event_id' => 5,
                'created_at' => '2024-06-12 21:55:02',
                'updated_at' => '2024-06-12 21:55:08',
                'deleted_at' => NULL,
            ],
            [
                'id' => 11,
                'details' => 'Toma de fotografías El Maguey',
                'completed' => 1,
                'event_id' => 5,
                'created_at' => '2024-06-12 21:55:02',
                'updated_at' => '2024-06-12 21:55:10',
                'deleted_at' => NULL,
            ],
            [
                'id' => 12,
                'details' => 'Entrevista vigilante Silao',
                'completed' => 1,
                'event_id' => 6,
                'created_at' => '2024-06-14 21:13:00',
                'updated_at' => '2024-06-19 18:24:47',
                'deleted_at' => NULL,
            ],
            [
                'id' => 13,
                'details' => 'Actividad laboral El Maguey',
                'completed' => 1,
                'event_id' => 6,
                'created_at' => '2024-06-14 21:13:00',
                'updated_at' => '2024-06-19 18:24:49',
                'deleted_at' => NULL,
            ],
            [
                'id' => 14,
                'details' => 'Seguimiento ST7 Javier Cuellar',
                'completed' => 1,
                'event_id' => 6,
                'created_at' => '2024-06-14 21:13:00',
                'updated_at' => '2024-06-19 18:24:51',
                'deleted_at' => NULL,
            ],
            [
                'id' => 15,
                'details' => 'Revisión de tema laboral 5 de Feb.',
                'completed' => 1,
                'event_id' => 7,
                'created_at' => '2024-06-14 21:20:10',
                'updated_at' => '2024-06-24 14:27:54',
                'deleted_at' => NULL,
            ],
            [
                'id' => 20,
                'details' => 'Atención de tema laboral Acámbaro',
                'completed' => 1,
                'event_id' => 12,
                'created_at' => '2024-06-20 22:29:48',
                'updated_at' => '2024-06-20 22:29:48',
                'deleted_at' => NULL,
            ],
            [
                'id' => 21,
                'details' => 'Junta con personal de limpieza',
                'completed' => 1,
                'event_id' => 7,
                'created_at' => '2024-06-20 22:31:08',
                'updated_at' => '2024-06-24 14:27:56',
                'deleted_at' => NULL,
            ],
            [
                'id' => 22,
                'details' => 'Baja 1',
                'completed' => 1,
                'event_id' => 22,
                'created_at' => '2024-06-26 19:53:48',
                'updated_at' => '2024-07-03 22:48:43',
                'deleted_at' => NULL,
            ],
            [
                'id' => 23,
                'details' => 'Baja 2',
                'completed' => 1,
                'event_id' => 22,
                'created_at' => '2024-06-26 19:53:48',
                'updated_at' => '2024-07-03 22:48:46',
                'deleted_at' => NULL,
            ],
            [
                'id' => 24,
                'details' => 'Revisión en juzgados de amparo de Juan Linares',
                'completed' => 1,
                'event_id' => 22,
                'created_at' => '2024-06-26 19:53:48',
                'updated_at' => '2024-07-03 22:48:45',
                'deleted_at' => NULL,
            ],
            [
                'id' => 25,
                'details' => 'Junta OT abiertas',
                'completed' => 1,
                'event_id' => 23,
                'created_at' => '2024-06-29 16:03:56',
                'updated_at' => '2024-07-01 20:31:48',
                'deleted_at' => NULL,
            ],
            [
                'id' => 26,
                'details' => 'Entrevista candidato vendedor 13:00 hrs',
                'completed' => 0,
                'event_id' => 23,
                'created_at' => '2024-06-29 16:03:56',
                'updated_at' => '2024-06-29 16:03:56',
                'deleted_at' => NULL,
            ],
            [
                'id' => 27,
                'details' => 'Revisión de horarios de comedor Refacciones',
                'completed' => 1,
                'event_id' => 23,
                'created_at' => '2024-06-29 16:03:56',
                'updated_at' => '2024-07-01 20:31:51',
                'deleted_at' => NULL,
            ],
            [
                'id' => 28,
                'details' => 'Platica de Admon de personal 5´s Qro.',
                'completed' => 1,
                'event_id' => 23,
                'created_at' => '2024-06-29 18:19:44',
                'updated_at' => '2024-07-01 20:31:56',
                'deleted_at' => NULL,
            ],
            [
                'id' => 29,
                'details' => 'Junta con gerente y gerentes departamentales para explicar el formato de auditoria de orden y limpieza que se aplicara a partir de julio 2024',
                'completed' => 1,
                'event_id' => 38,
                'created_at' => '2024-07-01 17:00:06',
                'updated_at' => '2024-07-01 17:00:06',
                'deleted_at' => NULL,
            ],
            [
                'id' => 30,
                'details' => 'Platica Admon de personal Silao',
                'completed' => 1,
                'event_id' => 25,
                'created_at' => '2024-07-05 15:58:33',
                'updated_at' => '2024-07-05 15:58:33',
                'deleted_at' => NULL,
            ],
            [
                'id' => 31,
                'details' => 'Revisión de nuevo esquema de trabajo Giovanna',
                'completed' => 1,
                'event_id' => 25,
                'created_at' => '2024-07-05 15:58:33',
                'updated_at' => '2024-07-05 15:58:33',
                'deleted_at' => NULL,
            ],
            [
                'id' => 32,
                'details' => 'Revisión de asignación de tareas para Andreina y Paty por renuncia de Yamileth',
                'completed' => 1,
                'event_id' => 24,
                'created_at' => '2024-07-05 15:59:23',
                'updated_at' => '2024-07-05 15:59:23',
                'deleted_at' => NULL,
            ],
        ];

        // Insertar los registros en la base de datos
        foreach ($activities as $activityData) {
            Activity::updateOrCreate(['id' => $activityData['id']], $activityData);
        }
    }
}
