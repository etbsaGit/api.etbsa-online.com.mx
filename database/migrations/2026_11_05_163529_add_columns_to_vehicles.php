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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->foreignId('marca_id')->nullable()->constrained('vehicles_marcas');
            $table->foreignId('modelo_id')->nullable()->constrained('vehicles_modelos');
            $table->foreignId('tipo_id')->nullable()->constrained('vehicles_tipos');
            $table->string('version')->nullable();
            $table->integer('anio')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['marca_id']);
            $table->dropForeign(['modelo_id']);
            $table->dropForeign(['tipo_id']);
            $table->dropColumn(['marca_id', 'modelo_id', 'tipo_id', 'version', 'anio']);
        });
    }
};
