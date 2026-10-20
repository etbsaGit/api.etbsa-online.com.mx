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
        Schema::create('vehicles_servicios_cat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_servicio_id')->constrained('vehicles_tipo_servicio');
            $table->string('nombre');
            $table->boolean('es_periodico');
            $table->integer('intervalo_km_sugerido')->nullable();
            $table->integer('intervalo_meses_sugerido')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles_servicios_cat');
    }
};
