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
        Schema::create('credito_historical', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('credito_solicitud');
            $table->foreignId('estatus_id')->constrained('estatus');
            $table->string('descripcion');
            $table->foreignId('empleado_id')->constrained('empleados');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credito_historical');
    }
};
