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
        Schema::create('survey_question_answers', function (Blueprint $table) {
            $table->id();

            $table->text('answer')->nullable();
            $table->text('comments')->nullable();

            $table->unsignedBigInteger('survey_question_id')->nullable();
            $table->unsignedBigInteger('survey_answer_id')->nullable();

            $table->foreign('survey_answer_id')->references('id')->on('survey_answers')->onDelete('set null');
            $table->foreign('survey_question_id')->references('id')->on('survey_questions')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_question_answers');
    }
};
