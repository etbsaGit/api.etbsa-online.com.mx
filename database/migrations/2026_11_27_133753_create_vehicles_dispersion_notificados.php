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
        Schema::create('vehicles_dispersion_notificados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispersion_id')->constrained('vehicles_dispersion_gasolina');
            $table->foreignId('notificado_id')->constrained('empleados');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles_dispersion_notificados');
    }
};
