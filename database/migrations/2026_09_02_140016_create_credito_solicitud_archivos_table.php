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
        Schema::create('credito_solicitud_archivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('credito_solicitud');
            $table->string('archivo');
            $table->string('path')->nullable();
            $table->string('extension');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credito_solicitud_archivos');
    }
};
