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
        Schema::create('asignaciones', function (Blueprint $table) {
            $table->id();

            $table->string('nombre');
            $table->string('descripcion');

            $table->unsignedBigInteger('tipo_de_asignacion_id')->nullable();
            $table->unsignedBigInteger('empleado_id')->nullable();

            $table->foreign('tipo_de_asignacion_id')->references('id')->on('tipos_de_asignaciones')->onDelete('set null');
            $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignaciones');
    }
};
