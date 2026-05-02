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
        Schema::table('tracking', function (Blueprint $table) {
            $table->tinyInteger('incluye_anticipo')->after('total')->nullable();
            $table->decimal('anticipo_monto',12,2)->after('incluye_anticipo')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tracking', function (Blueprint $table) {
            $table->dropColumn('incluye_anticipo');
            $table->dropColumn('anticipo_monto');
        });
    }
};
