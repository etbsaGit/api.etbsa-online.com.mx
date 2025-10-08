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
        Schema::create('inversiones_agricolas', function (Blueprint $table) {
            $table->id();

            $table->integer('year');
            $table->string('ciclo');
            $table->integer('hectareas');
            $table->integer('costo');

            $table->unsignedBigInteger('ganado_id')->nullable();
            $table->foreign('ganado_id')->references('id')->on('ganados')->onDelete('restrict');

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
        Schema::dropIfExists('inversiones_agricolas');
    }
};
