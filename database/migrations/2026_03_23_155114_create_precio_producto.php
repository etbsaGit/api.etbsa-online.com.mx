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
        Schema::create('precio_producto', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('producto_id');
            $table->unsignedBigInteger('condicion_pago_id');
            $table->unsignedBigInteger('currency_id');
            $table->foreign('producto_id')->references('id')->on('products')->onDelete('restrict');
            $table->foreign('condicion_pago_id')->references('id')->on('products_condicion_pago')->onDelete('restrict');
            $table->foreign('currency_id')->references('id')->on('currency')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('precio_producto');
    }
};
