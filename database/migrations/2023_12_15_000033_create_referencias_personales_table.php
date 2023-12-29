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
        Schema::create('referencias_personales', function (Blueprint $table) {
            $table->id();

            $table->string('nombre');
            $table->string('telefono');
            $table->string('parentesco');
            $table->string('direccion');

            $table->unsignedBigInteger('empleado_id')->nullable();

            $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referencias_personales');
    }
};
