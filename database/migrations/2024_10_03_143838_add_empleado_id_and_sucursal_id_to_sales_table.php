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
        Schema::table('sales', function (Blueprint $table) {
            $table->unsignedBigInteger('empleado_id')->nullable();
            $table->unsignedBigInteger('sucursal_id')->nullable();

            // Agregar las llaves foráneas con restricción de eliminación
            $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('restrict');
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Eliminar las llaves foráneas primero
            $table->dropForeign(['empleado_id']);
            $table->dropForeign(['sucursal_id']);

            // Luego eliminar las columnas
            $table->dropColumn(['empleado_id', 'sucursal_id']);
        });
    }
};
