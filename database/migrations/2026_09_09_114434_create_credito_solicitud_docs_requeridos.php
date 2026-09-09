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
        Schema::create('credito_solicitud_docs_requeridos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->foreignId('doc_id')->constrained('credito_docs_solicitados');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credito_solicitud_docs_requeridos');
    }
};
