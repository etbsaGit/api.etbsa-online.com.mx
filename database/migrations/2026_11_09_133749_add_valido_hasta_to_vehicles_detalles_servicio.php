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
        Schema::table('vehicles_detalles_servicio', function (Blueprint $table) {
            $table->date('valido_hasta')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles_detalles_servicio', function (Blueprint $table) {
            $table->dropColumn('valido_hasta');
        });
    }
};
