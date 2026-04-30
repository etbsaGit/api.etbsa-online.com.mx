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
            //
            $table->unsignedBigInteger('prospecto_id')->nullable()->after('folio');
            $table->unsignedBigInteger('cliente_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tracking', function (Blueprint $table) {
            //
            $table->dropColumn('prospecto_id');
            $table->unsignedBigInteger('cliente_id')->after('folio');
        });
    }
};
