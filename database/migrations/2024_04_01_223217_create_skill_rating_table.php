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
        Schema::create('skill_rating', function (Blueprint $table) {
            $table->id();

            $table->integer('rating');

            $table->unsignedBigInteger('skill_id');
            $table->unsignedBigInteger('empleado_id');

            $table->foreign('skill_id')->references('id')->on('skills')->onDelete('cascade');
            $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skill_rating');
    }
};
