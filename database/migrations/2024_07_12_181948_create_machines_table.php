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
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->string('serie');
            $table->string('modelo');
            $table->integer('anio');
            $table->integer('valor');

            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('restrict');

            $table->unsignedBigInteger('marca_id')->nullable();
            $table->foreign('marca_id')->references('id')->on('marcas')->onDelete('restrict');

            $table->unsignedBigInteger('condicion_id')->nullable();
            $table->foreign('condicion_id')->references('id')->on('condiciones')->onDelete('restrict');

            $table->unsignedBigInteger('clas_equipo_id')->nullable();
            $table->foreign('clas_equipo_id')->references('id')->on('clas_equipos')->onDelete('restrict');

            $table->unsignedBigInteger('tipo_equipo_id')->nullable();
            $table->foreign('tipo_equipo_id')->references('id')->on('tipos_equipo')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machines');
    }
};
