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
        Schema::create('credito_solicitud_aplazar_pago', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pago_id')->constrained('credito_historial_pagos');
            $table->foreignId('solicitante_id')->constrained('empleados');
            $table->foreignId('estatus_id')->constrained('estatus');
            $table->foreignId('validated_by')->constrained('empleados')->nullable();
            $table->date('fecha_actual');
            $table->date('fecha_nueva');
            $table->text('motivo');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credito_solicitud_aplazar_pago');
    }
};
