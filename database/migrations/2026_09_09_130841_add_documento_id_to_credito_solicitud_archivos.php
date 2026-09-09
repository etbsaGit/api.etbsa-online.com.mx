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
        Schema::table('credito_solicitud_archivos', function (Blueprint $table) {
            $table->foreignId('documento_id')->constrained('credito_docs_solicitados');
            $table->dropColumn('archivo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credito_solicitud_archivos', function (Blueprint $table) {
            $table->string('archivo');
            $table->dropForeign(['documento_id']);
            $table->dropColumn('documento_id');
        });
    }
};
