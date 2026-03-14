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
        Schema::create('tractor_pago_detalle', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('condicion_pago_id');
            $table->foreign('condicion_pago_id')->references('id')->on('condicion_pago_tractor')->onDelete('restrict');
            $table->integer('numero_pago');
            $table->decimal('monto_pago', 10, 2);
            $table->date('fecha_pago')->nullable();
            $table->string('comments')->nullable();
            $table->unsignedBigInteger('metodo_pago_id');
            $table->foreign('metodo_pago_id')->references('id')->on('caja_tipos_pagos')->onDelete('restrict');
            $table->unsignedBigInteger('estatus_id')->nullable();
            $table->foreign('estatus_id')->references('id')->on('estatus')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tractor_pago_detalle');
    }
};
