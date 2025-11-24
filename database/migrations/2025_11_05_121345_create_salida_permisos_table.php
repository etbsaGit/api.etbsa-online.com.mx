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
        Schema::create('salida_permisos', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->time('start');
            $table->time('end');
            $table->time('lunch_start');
            $table->time('lunch_end');

            $table->boolean('status')->nullable();

            $table->string('description')->nullable();

            $table->string('feedback')->nullable();

            $table->unsignedBigInteger('empleado_id')->nullable();
            $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('restrict');

            $table->unsignedBigInteger('sucursal_id')->nullable();
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salida_permisos');
    }
};
