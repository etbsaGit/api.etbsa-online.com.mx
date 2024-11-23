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
        Schema::table('sales', function (Blueprint $table) {
            $table->unsignedBigInteger('referencia_id')->nullable();

            // Clave foránea con restricción
            $table->foreign('referencia_id')->references('id')->on('referencias')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Eliminar la clave foránea
            $table->dropForeign(['referencia_id']);
            // Eliminar la columna
            $table->dropColumn('referencia_id');
        });
    }
};
