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
        Schema::table('credito_solicitud', function (Blueprint $table) {
            $table->renameColumn('anticipo', 'valor_enganche');
            $table->foreignId('tipo_enganche_id')->constrained('credito_tipo_enganche');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credito_solicitud', function (Blueprint $table) {
            $table->renameColumn('valor_enganche', 'anticipo');
            $table->dropForeign(['tipo_enganche_id']);
            $table->dropColumn('tipo_enganche_id');
        });
    }
};
