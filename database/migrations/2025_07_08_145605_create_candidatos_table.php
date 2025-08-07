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
        Schema::create('candidatos', function (Blueprint $table) {
            $table->id();

            $table->string('nombre');
            $table->string('telefono')->nullable();
            $table->string('cv');
            $table->string('status_1');
            $table->date('fecha_entrevista_1')->nullable();
            $table->string('forma_reclutamiento');
            $table->string('status_2');
            $table->string('fecha_ingreso')->nullable();

            $table->unsignedBigInteger('requisicion_id')->nullable();
            $table->foreign('requisicion_id')->references('id')->on('requisicion_personals')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidatos');
    }
};
