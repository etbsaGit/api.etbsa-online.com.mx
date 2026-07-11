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
        Schema::create('historical_dim_EquipoCliente', function (Blueprint $table) {
            $table->id('equipo_key');
            $table->unsignedBigInteger('cliente_key')->nullable();
            $table->string('numero_serie')->nullable();
            $table->string('modelo')->nullable();
            $table->string('marca')->nullable();
            $table->string('tipo_equipo')->nullable();
            $table->unsignedBigInteger('anio')->nullable();
            $table->string('estado')->nullable();
            $table->decimal('valor',15,2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historical_dim_EquipoCliente');
    }
};
