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
        Schema::create('sales_tractors', function (Blueprint $table) {
            $table->id();
            $table->string('order');
            $table->unsignedBigInteger('vendedor_id');
            $table->foreign('vendedor_id')->references('id')->on('empleados');
            $table->unsignedBigInteger('sucursal_id');
            $table->foreign('sucursal_id')->references('id')->on('sucursales');
            $table->unsignedBigInteger('cliente_id');
            $table->foreign('cliente_id')->references('id')->on('clientes');
            $table->unsignedBigInteger('inv_model_id');
            $table->foreign('inv_model_id')->references('id')->on('inv_models')->onDelete('restrict');
            $table->date('fecha');
            $table->decimal('total', 15, 2);
            $table->unsignedBigInteger('condicion_pago_id');
            $table->foreign('condicion_pago_id')->references('id')->on('estatus');
            $table->unsignedBigInteger('referencia_cliente_id')->nullable();
            $table->foreign('referencia_cliente_id')->references('id')->on('referencias')->onDelete('set null');
            $table->unsignedBigInteger('estatus_id')->nullable();
            $table->foreign('estatus_id')->references('id')->on('estatus')->onDelete('restrict');
            $table->string('comments')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_tractors');
    }
};
