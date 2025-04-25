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
        Schema::create('caja_cuentas', function (Blueprint $table) {
            $table->id();

            $table->string('numeroCuenta');
            $table->string('descripcion');

            $table->unsignedBigInteger('caja_banco_id')->nullable();
            $table->foreign('caja_banco_id')->references('id')->on('caja_bancos')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caja_cuentas');
    }
};
