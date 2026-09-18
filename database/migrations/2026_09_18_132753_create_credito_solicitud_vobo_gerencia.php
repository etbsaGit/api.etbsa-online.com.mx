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
        Schema::create('credito_solicitud_vobo_gerencia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('credito_solicitud');
            $table->foreignId('empleado_id')->constrained('empleados');
            $table->foreignId('estatus_id')->constrained('estatus');
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credito_solicitud_vobo_gerencia');
    }
};
