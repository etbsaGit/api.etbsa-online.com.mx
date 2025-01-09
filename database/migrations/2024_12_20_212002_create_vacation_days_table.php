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
        Schema::create('vacation_days', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('empleado_id');
            $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('restrict');

            $table->unsignedBigInteger('sucursal_id');
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('restrict');

            $table->unsignedBigInteger('puesto_id');
            $table->foreign('puesto_id')->references('id')->on('puestos')->onDelete('restrict');

            $table->string('vehiculo_utilitario')->nullable();
            $table->string('periodo_correspondiente');
            $table->integer('anios_cumplidos');
            $table->integer('dias_periodo');
            $table->integer('subtotal_dias');
            $table->integer('dias_disfrute');
            $table->integer('dias_pendientes');
            $table->date('fecha_inicio');
            $table->date('fecha_termino');
            $table->date('fecha_regreso');
            $table->boolean('validated')->nullable();
            $table->string('comentarios')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacation_days');
    }
};
