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
        Schema::create('caja_cortes', function (Blueprint $table) {
            $table->id();
            $table->decimal('efectivo', 8, 2);
            $table->decimal('tarjeta_debito', 8, 2);
            $table->decimal('tarjeta_credito', 8, 2);
            $table->decimal('transferencias', 8, 2);
            $table->decimal('depositos', 8, 2);
            $table->decimal('cheques', 8, 2);

            $table->date('fecha_corte');

            $table->string('comentarios');

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');

            $table->unsignedBigInteger('susursal_id')->nullable();
            $table->foreign('susursal_id')->references('id')->on('sucursales')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caja_cortes');
    }
};
