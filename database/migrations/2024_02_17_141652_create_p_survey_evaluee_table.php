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
        Schema::create('p_survey_evaluee', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('evaluee_id')->nullable();
            $table->unsignedBigInteger('survey_id')->nullable();

            $table->foreign('survey_id')->references('id')->on('surveys')->onDelete('set null');
            $table->foreign('evaluee_id')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('p_survey_evaluee');
    }
};
