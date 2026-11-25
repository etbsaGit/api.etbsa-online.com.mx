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
        Schema::create('vehicles_checklist_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->constrained('vehicles_checklist');
            $table->string('documento');
            $table->boolean('existencia');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles_checklist_detalle');
    }
};
