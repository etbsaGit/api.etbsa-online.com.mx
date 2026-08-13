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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('expo_token')->unique();
            $table->string('platform')->nullable();
            $table->string('brand')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('model_name')->nullable();
            $table->string('os_version')->nullable();
            $table->string('application_id')->nullable();
            $table->timestamp('linked_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
