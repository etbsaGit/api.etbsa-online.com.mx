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
        Schema::create('credito_lineas_enganche', function (Blueprint $table) {
            $table->id();
            $table->foreignId('linea_id')->constrained('credito_lineas');
            $table->foreignId('tipo_id')->constrained('credito_tipo_enganche');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credito_lineas_enganche');
    }
};
