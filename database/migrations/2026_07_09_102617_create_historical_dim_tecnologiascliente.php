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
        Schema::create('historical_dim_TecnologiasCliente', function (Blueprint $table) {
            $table->id('tecnologia_key');
            $table->unsignedBigInteger('cliente_key');
            $table->string('tecnologia')->nullable();
            $table->decimal('h_conectadas',10,2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historical_dim_TecnologiasCliente');
    }
};
