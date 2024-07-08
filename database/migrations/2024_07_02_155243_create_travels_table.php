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
        Schema::create('travels', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('start_point')->nullable();
            $table->foreign('start_point')->references('id')->on('sucursales');

            $table->unsignedBigInteger('end_point')->nullable();
            $table->foreign('end_point')->references('id')->on('sucursales');

            // Columnas adicionales
            $table->time('start_time');
            $table->time('end_time');

            // Relación con la tabla events (event_id)
            $table->unsignedBigInteger('event_id')->nullable();
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travels');
    }
};
