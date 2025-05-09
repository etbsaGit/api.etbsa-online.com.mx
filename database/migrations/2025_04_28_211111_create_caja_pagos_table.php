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
        Schema::create('caja_pagos', function (Blueprint $table) {
            $table->id();

            $table->decimal('monto', 9, 2);
            $table->string('descripcion');

            $table->unsignedBigInteger('sucursal_id')->nullable();
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('restrict');

            $table->unsignedBigInteger('categoria_id')->nullable();
            $table->foreign('categoria_id')->references('id')->on('caja_categorias')->onDelete('restrict');

            $table->unsignedBigInteger('transaccion_id')->nullable();
            $table->foreign('transaccion_id')->references('id')->on('caja_transacciones')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caja_pagos');
    }
};
