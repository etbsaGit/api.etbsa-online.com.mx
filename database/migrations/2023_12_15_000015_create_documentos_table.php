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
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();

            $table->string('nombre');
            $table->date('fecha_de_vencimiento');
            $table->string('descripcion')->nullable();

            $table->unsignedBigInteger('requisito_id')->nullable();
            $table->unsignedBigInteger('expediente_id')->nullable();
            $table->unsignedBigInteger('estatus_id')->nullable();

            $table->foreign('requisito_id')->references('id')->on('requisitos')->onDelete('set null');
            $table->foreign('expediente_id')->references('id')->on('expedientes')->onDelete('set null');
            $table->foreign('estatus_id')->references('id')->on('estatus')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
