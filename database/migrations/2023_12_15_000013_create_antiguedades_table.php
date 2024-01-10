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
        Schema::create('antiguedades', function (Blueprint $table) {
            $table->id();

            $table->integer('años_cumplidos');
            $table->integer('dias_correspondientes');
            $table->enum('regimen', [2022,2023])->default(2023);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('antiguedades');
    }
};
