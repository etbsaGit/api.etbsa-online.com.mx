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
        Schema::create('p_skills_puestos', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('skill_id');
            $table->unsignedBigInteger('puesto_id');

            $table->foreign('skill_id')->references('id')->on('skills')->onDelete('cascade');
            $table->foreign('puesto_id')->references('id')->on('puestos')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('p_skills_puestos');
    }
};
