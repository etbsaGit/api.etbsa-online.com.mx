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
        Schema::create('p_sucursales_lineas', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('sucursal_id')->nullable();
            $table->unsignedBigInteger('linea_id')->nullable();

            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('set null');
            $table->foreign('linea_id')->references('id')->on('lineas')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('p_sucursales_lineas');
    }
};
