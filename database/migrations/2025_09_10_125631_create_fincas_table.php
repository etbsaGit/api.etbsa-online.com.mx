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
        Schema::create('fincas', function (Blueprint $table) {
            $table->id();

            $table->string('nombre');
            $table->string('descripcion');
            $table->integer('valor')->nullable();
            $table->integer('costo')->nullable();

            $table->unsignedBigInteger('estatus_id')->nullable();
            $table->foreign('estatus_id')->references('id')->on('estatus')->onDelete('restrict');

            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fincas');
    }
};
