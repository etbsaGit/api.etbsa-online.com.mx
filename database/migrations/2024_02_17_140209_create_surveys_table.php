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
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();

            $table->string('image')->nullable();
            $table->string('title');
            $table->string('slug');
            $table->tinyInteger('status');
            $table->text('description')->nullable();
            $table->timestamp('expire_date')->nullable();

            $table->unsignedBigInteger('evaluator_id')->nullable();

            $table->foreign('evaluator_id')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surveys');
    }
};
