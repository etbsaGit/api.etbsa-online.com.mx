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
        Schema::create('caja_detalle_efectivos', function (Blueprint $table) {
            $table->id();

            $table->integer('cantidad');

            $table->unsignedBigInteger('denominacion_id')->nullable();
            $table->foreign('denominacion_id')->references('id')->on('caja_denominaciones')->onDelete('restrict');

            $table->unsignedBigInteger('corte_id')->nullable();
            $table->foreign('corte_id')->references('id')->on('caja_cortes')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caja_detalle_efectivos');
    }
};
