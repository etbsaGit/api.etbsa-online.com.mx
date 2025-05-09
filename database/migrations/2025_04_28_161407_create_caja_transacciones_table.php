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
        Schema::create('caja_transacciones', function (Blueprint $table) {
            $table->id();
            $table->string('factura');
            $table->string('folio');
            $table->string('serie');
            $table->string('uuid');
            $table->string('comentarios');
            $table->boolean('validado');
            $table->date('fecha_pago');

            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('restrict');

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');

            $table->unsignedBigInteger('tipo_factura_id')->nullable();
            $table->foreign('tipo_factura_id')->references('id')->on('caja_tipos_facturas')->onDelete('restrict');

            $table->unsignedBigInteger('cuenta_id')->nullable();
            $table->foreign('cuenta_id')->references('id')->on('caja_cuentas')->onDelete('restrict');

            $table->unsignedBigInteger('tipo_pago_id')->nullable();
            $table->foreign('tipo_pago_id')->references('id')->on('caja_tipos_pagos')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caja_transacciones');
    }
};
