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
        Schema::create('credito_solicitud', function (Blueprint $table) {
            $table->id();
            $table->string('folio');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('asesor_id')->constrained('empleados');
            $table->foreignId('estatus_id')->constrained('estatus');
            $table->foreignId('notificado_id')->constrained('empleados');
            $table->string('motivo');
            $table->foreignId('validated_by')->nullable()->constrained('empleados');
            $table->decimal('monto_solicitado', 12, 2);
            $table->foreignId('linea_id')->constrained('credito_lineas');
            $table->unsignedBigInteger('numero_pagos');
            $table->string('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credito_solicitud');
    }
};
