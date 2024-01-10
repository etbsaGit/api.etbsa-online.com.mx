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
        Schema::create('p_plantillas_requisitos', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('plantilla_id')->nullable();
            $table->unsignedBigInteger('requisito_id')->nullable();

            $table->foreign('plantilla_id')->references('id')->on('plantillas')->onDelete('set null');
            $table->foreign('requisito_id')->references('id')->on('requisitos')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('p_plantillas_requisitos');
    }
};
