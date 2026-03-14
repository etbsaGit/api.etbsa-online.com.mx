<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('estatus')->insert([
            [
            'nombre' => 'Pedido',
            'clave' => 'pedido',
            'tipo_estatus' => 'sale',
            'color' => '#1b98ff',
            'created_at' => now(),
            'updated_at' => now(),
            ],
            [
            'nombre' => 'VoBo Gerencia Territorial',
            'clave' => 'vobo_gerencia_territorial',
            'tipo_estatus' => 'sale',
            'color' => '#1b98ff',
            'created_at' => now(),
            'updated_at' => now(),
            ],
            [
            'nombre' => 'VoBo Gerencia Corptoprativa',
            'clave' => 'vobo_gerencia_corporativa',
            'tipo_estatus' => 'sale',
            'color' => '#1b98ff',
            'created_at' => now(),
            'updated_at' => now(),
            ],
            [
            'nombre' => 'Asignado',
            'clave' => 'asignado',
            'tipo_estatus' => 'sale',
            'color' => '#1b98ff',
            'created_at' => now(),
            'updated_at' => now(),
            ],
            [
            'nombre' => 'Completado',
            'clave' => 'completado',
            'tipo_estatus' => 'sale',
            'color' => '#1bff45',
            'created_at' => now(),
            'updated_at' => now(),
            ],
            [
            'nombre' => 'Cancelado',
            'clave' => 'cancelado',
            'tipo_estatus' => 'sale',
            'color' => '#ff4545',
            'created_at' => now(),
            'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
