<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('condicion_pago_categorias', function (Blueprint $table) {
            $table->unsignedBigInteger('condicion_id');
            $table->unsignedBigInteger('categoria_id');

            $table->timestamps();

            //  clave primaria compuesta
            $table->primary(['condicion_id', 'categoria_id']);

            //  llaves foráneas
            $table->foreign('condicion_id')
                ->references('id')
                ->on('products_condicion_pago')
                ->onDelete('cascade');

            $table->foreign('categoria_id')
                ->references('id')
                ->on('categories')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('condicion_pago_categorias');
    }
};
