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
        Schema::create('horas_technicians', function (Blueprint $table) {
            $table->id();

            $table->integer('mes');
            $table->integer('anio');
            $table->integer('facturadas');
            $table->integer('con_ingreso');

            $table->unsignedBigInteger('tecnico_id')->nullable();
            $table->foreign('tecnico_id')->references('id')->on('empleados')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horas_technicians');
    }
};
