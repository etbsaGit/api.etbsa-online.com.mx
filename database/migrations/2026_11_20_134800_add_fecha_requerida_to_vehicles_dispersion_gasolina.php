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
        if (!Schema::hasColumn('vehicles_dispersion_gasolina', 'fecha_requerida')) {
            Schema::table('vehicles_dispersion_gasolina', function (Blueprint $table) {
                $table->dateTime('fecha_requerida')->nullable()->after('km_evento');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('vehicles_dispersion_gasolina', 'fecha_requerida')) {
            Schema::table('vehicles_dispersion_gasolina', function (Blueprint $table) {
                $table->dropColumn('fecha_requerida');
            });
        }
    }
};
