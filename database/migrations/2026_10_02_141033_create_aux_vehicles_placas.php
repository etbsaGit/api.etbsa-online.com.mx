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
        Schema::create('aux_vehicles_placas', function (Blueprint $table) {
            $table->id();
            $table->string('placas');
            $table->string('serie');
            $table->string('placas1')->nullable();
            $table->string('placas2')->nullable();
            $table->string('placas3')->nullable();
            $table->string('placas4')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aux_vehicles_placas');
    }
};
