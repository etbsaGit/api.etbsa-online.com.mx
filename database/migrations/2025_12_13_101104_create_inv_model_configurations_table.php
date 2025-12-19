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
        Schema::create('inv_configuration_inv_model', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('inv_model_id')->nullable();
            $table->foreign('inv_model_id')->references('id')->on('inv_models')->onDelete('restrict');

            $table->unsignedBigInteger('inv_configuration_id')->nullable();
            $table->foreign('inv_configuration_id')->references('id')->on('inv_configurations')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_configuration_inv_model');
    }
};
