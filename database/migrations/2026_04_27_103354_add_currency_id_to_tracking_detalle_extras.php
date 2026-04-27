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
        Schema::table('tracking_detalle_extras', function (Blueprint $table) {
            $table->unsignedBigInteger('currency_id')->after('subtotal')->nullable();
                $table->foreign('currency_id')->references('id')->on('currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tracking_detalle_extras', function (Blueprint $table) {
            $table->dropColumn('currency_id');
        });
    }
};
