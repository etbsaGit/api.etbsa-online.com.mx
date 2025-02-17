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
        Schema::create('prospect_riegos', function (Blueprint $table) {
            $table->id();

            $table->integer('hectareas_propias');
            $table->integer('hectareas_rentadas');

            $table->string('marca');

            $table->unsignedBigInteger('prospect_id')->nullable();
            $table->foreign('prospect_id')->references('id')->on('prospects')->onDelete('restrict');

            $table->unsignedBigInteger('riego_id')->nullable();
            $table->foreign('riego_id')->references('id')->on('riegos')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prospect_riegos');
    }
};
