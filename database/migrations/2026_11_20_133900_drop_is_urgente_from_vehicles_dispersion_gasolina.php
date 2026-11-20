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
        if (Schema::hasColumn('vehicles_dispersion_gasolina', 'is_urgente')) {
            Schema::table('vehicles_dispersion_gasolina', function (Blueprint $table) {
                $table->dropColumn('is_urgente');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('vehicles_dispersion_gasolina', 'is_urgente')) {
            Schema::table('vehicles_dispersion_gasolina', function (Blueprint $table) {
                $table->boolean('is_urgente')->default(false)->after('estatus_id');
            });
        }
    }
};
