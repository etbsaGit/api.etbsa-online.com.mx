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
        Schema::create('candidato_notas', function (Blueprint $table) {
            $table->id();

            $table->string('nota');

            $table->unsignedBigInteger('candidato_id')->nullable();
            $table->foreign('candidato_id')->references('id')->on('candidatos')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidato_notas');
    }
};
