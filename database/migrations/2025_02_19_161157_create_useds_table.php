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
        Schema::create('useds', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->string('description');

            $table->string('comments');

            $table->boolean('status')->nullable();

            $table->string('serial');

            $table->string('year');

            $table->integer('hours');

            $table->integer('cost')->nullable();

            $table->integer('price')->nullable();

            $table->unsignedBigInteger('origin_id')->nullable();
            $table->foreign('origin_id')->references('id')->on('sucursales')->onDelete('restrict');

            $table->unsignedBigInteger('location_id')->nullable();
            $table->foreign('location_id')->references('id')->on('sucursales')->onDelete('restrict');

            $table->unsignedBigInteger('tipo_equipo_id')->nullable();
            $table->foreign('tipo_equipo_id')->references('id')->on('tipos_equipo')->onDelete('restrict');

            $table->unsignedBigInteger('linea_id')->nullable();
            $table->foreign('linea_id')->references('id')->on('lineas')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('useds');
    }
};
