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
        if (!Schema::hasColumn('vehicles_dispersion_gasolina', 'recorrido_url')) {
            Schema::table('vehicles_dispersion_gasolina', function (Blueprint $table) {
                $table->text('recorrido_url')->nullable()->after('fecha_requerida');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('vehicles_dispersion_gasolina', 'recorrido_url')) {
            Schema::table('vehicles_dispersion_gasolina', function (Blueprint $table) {
                $table->dropColumn('recorrido_url');
            });
        }
    }
};
