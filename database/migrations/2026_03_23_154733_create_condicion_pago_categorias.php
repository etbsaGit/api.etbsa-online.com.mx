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
        Schema::create('condicion_pago_categorias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('condicion_id');
            $table->unsignedBigInteger('categoria_id');
            $table->foreign('condicion_id')->references('id')->on('products_condicion_pago')->onDelete('restrict');
            $table->foreign('categoria_id')->references('id')->on('categories')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('condicion_pago_categorias');
    }
};
