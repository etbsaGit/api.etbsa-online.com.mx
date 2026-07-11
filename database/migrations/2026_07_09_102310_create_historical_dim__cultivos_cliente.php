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
        Schema::create('historical_dim_CultivosCliente', function (Blueprint $table) {
            $table->id('cultivo_key');
            $table->unsignedBigInteger('cliente_key');
            $table->string('nombre_cultivo')->nullable();
            $table->unsignedBigInteger('anio')->nullable();
            $table->string('ciclo')->nullable();
            $table->decimal('hectareas',10,2)->nullable();
            $table->decimal('costo',15,2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historical_dim_CultivosCliente');
    }
};
