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
        Schema::create('incapacities', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('empleado_id');
            $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('restrict');

            $table->unsignedBigInteger('sucursal_id');
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('restrict');

            $table->unsignedBigInteger('puesto_id');
            $table->foreign('puesto_id')->references('id')->on('puestos')->onDelete('restrict');

            $table->unsignedBigInteger('estatus_id');
            $table->foreign('estatus_id')->references('id')->on('estatus')->onDelete('restrict');

            $table->date('fecha_inicio');
            $table->date('fecha_termino');
            $table->date('fecha_regreso');
            $table->string('comentarios')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incapacities');
    }
};
