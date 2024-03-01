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
        Schema::create('survey_answers', function (Blueprint $table) {
            $table->id();

            $table->longText('answer');
            $table->longText('comments')->nullable();
            $table->integer('rating')->nullable();

            $table->unsignedBigInteger('evaluee_id')->nullable();
            $table->unsignedBigInteger('question_id')->nullable();

            $table->foreign('question_id')->references('id')->on('survey_questions')->onDelete('set null');
            $table->foreign('evaluee_id')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_answers');
    }
};
