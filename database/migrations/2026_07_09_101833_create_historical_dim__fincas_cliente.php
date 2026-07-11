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
        Schema::create('historical_dim_FincasCliente', function (Blueprint $table) {
            $table->id('finca_key');
            $table->unsignedBigInteger('cliente_key');
            $table->string('nombre')->nullable();
            $table->string('descripcion')->nullable();
            $table->string('propiedad')->nullable();
            $table->decimal('valor',10,2)->nullable();
            $table->decimal('costo',10,2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historical_dim_FincasCliente');
    }
};
