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
        Schema::create('experiencias_laborales', function (Blueprint $table) {
            $table->id();

            $table->string('Lugar');
            $table->date('Inicio');
            $table->date('Termino');
            $table->string('Telefono');
            $table->string('direccion');

            $table->unsignedBigInteger('puesto_id')->nullable();
            $table->unsignedBigInteger('empleado_id')->nullable();


            $table->foreign('puesto_id')->references('id')->on('puestos')->onDelete('set null');
            $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('set null');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experiencias_laborales');
    }
};
