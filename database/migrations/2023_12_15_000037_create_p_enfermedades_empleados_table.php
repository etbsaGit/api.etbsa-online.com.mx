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
        Schema::create('p_enfermedades_empleados', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('enfermedad_id')->nullable();
            $table->unsignedBigInteger('empleado_id')->nullable();

            $table->foreign('enfermedad_id')->references('id')->on('enfermedades')->onDelete('set null');
            $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('p_enfermedades_empleados');
    }
};
