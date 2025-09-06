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
        Schema::create('credito_relacions', function (Blueprint $table) {
            $table->id();

            $table->integer('valor');

            $table->unsignedBigInteger('credito_declaracion_id');
            $table->foreign('credito_declaracion_id')->references('id')->on('credito_declaracions')->onDelete('restrict');

            $table->unsignedBigInteger('credito_concepto_id');
            $table->foreign('credito_concepto_id')->references('id')->on('credito_conceptos')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credito_relacions');
    }
};
