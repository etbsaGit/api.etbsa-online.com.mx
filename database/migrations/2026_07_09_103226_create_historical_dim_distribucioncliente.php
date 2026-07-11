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
        Schema::create('historical_dim_DistribucionCliente', function (Blueprint $table) {
            $table->id('distribucion_key');
            $table->unsignedBigInteger('cliente_key');
            $table->decimal('total_hectareas',10,2)->nullable();
            $table->decimal('hectareas_propias',10,2)->nullable();
            $table->decimal('hectareas_rentadas',10,2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historical_dim_DistribucionCliente');
    }
};
