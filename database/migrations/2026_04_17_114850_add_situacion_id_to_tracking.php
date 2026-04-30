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
            $table->unsignedBigInteger('situacion_id')->nullable()->after('estatus_id');
                $table->foreign('situacion_id')->references('id')->on('estatus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tracking', function (Blueprint $table) {
            //
        });
    }
};
