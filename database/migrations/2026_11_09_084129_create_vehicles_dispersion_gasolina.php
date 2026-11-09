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
        Schema::create('vehicles_dispersion_gasolina', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles');
            $table->foreignId('solicitado_por')->nullable()->constrained('empleados');
            $table->foreignId('registrado_por')->nullable()->constrained('users');
            $table->foreignId('suc_cargo_id')->nullable()->constrained('vehicles_suc_cargo_a');
            $table->foreignId('depto_cargo_id')->nullable()->constrained('vehicles_depto_cargo_a');
            $table->integer('lts_solicitados');
            $table->integer('lts_dispersados')->nullable();
            $table->integer('km_evento');
            $table->text('notas')->nullable();
            $table->foreignId('estatus_id')->constrained('estatus');
            $table->boolean('is_urgente')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles_dispersion_gasolina');
    }
};
