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
        Schema::create('estudios', function (Blueprint $table) {
            $table->id();

            $table->date('inicio');
            $table->date('termino')->nullable();

            $table->unsignedBigInteger('documentoQueAvala_id')->nullable();
            $table->unsignedBigInteger('estadoDelEstudio_id')->nullable();
            $table->unsignedBigInteger('escuela_id')->nullable();
            $table->unsignedBigInteger('escolaridad_id')->nullable();
            $table->unsignedBigInteger('empleado_id')->nullable();

            $table->foreign('documentoQueAvala_id')->references('id')->on('documentos_que_avalan')->onDelete('set null');
            $table->foreign('estadoDelEstudio_id')->references('id')->on('estados_del_estudio')->onDelete('set null');
            $table->foreign('escuela_id')->references('id')->on('escuelas')->onDelete('set null');
            $table->foreign('escolaridad_id')->references('id')->on('escolaridades')->onDelete('set null');
            $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estudios');
    }
};
