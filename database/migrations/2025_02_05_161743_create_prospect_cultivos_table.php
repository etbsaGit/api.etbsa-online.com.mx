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
        Schema::create('prospect_cultivos', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('prospect_id')->nullable();
            $table->foreign('prospect_id')->references('id')->on('prospects')->onDelete('restrict');

            $table->unsignedBigInteger('cultivo_id')->nullable();
            $table->foreign('cultivo_id')->references('id')->on('cultivos')->onDelete('restrict');

            $table->unsignedBigInteger('tipo_cultivo_id')->nullable();
            $table->foreign('tipo_cultivo_id')->references('id')->on('tipos_cultivo')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prospect_cultivos');
    }
};
