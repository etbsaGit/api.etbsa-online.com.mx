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
        Schema::table('vehicles_ordenes_servicio', function (Blueprint $table) {
            $table->foreignId('estatus_id')->nullable()->constrained('estatus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles_ordenes_servicio', function (Blueprint $table) {
            $table->dropForeign(['estatus_id']);
            $table->dropColumn('estatus_id');
        });
    }
};
