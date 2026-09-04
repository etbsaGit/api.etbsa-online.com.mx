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
        Schema::create('credito_historial_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('credito_solicitud');
            $table->unsignedInteger('n_pago');
            $table->decimal('monto_pagado', 12, 2)->nullable();
            $table->decimal('saldo_pendiente', 12, 2);
            $table->date('fecha_a_pagar');
            $table->date('fecha_liquidado')->nullable();
            $table->foreignId('estatus_id')->constrained('estatus');
            $table->foreignId('validated_by')->nullable()->constrained('empleados');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credito_historial_pagos');
    }
};
