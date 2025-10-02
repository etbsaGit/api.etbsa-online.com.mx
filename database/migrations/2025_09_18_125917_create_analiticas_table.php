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
        Schema::create('analiticas', function (Blueprint $table) {
            $table->id();

            $table->integer('efectivo')->nullable();
            $table->integer('caja')->nullable();

            $table->integer('gastos')->nullable();

            $table->boolean('status')->nullable();

            $table->date('fecha');

            $table->unsignedBigInteger('cliente_id');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analiticas');
    }
};
