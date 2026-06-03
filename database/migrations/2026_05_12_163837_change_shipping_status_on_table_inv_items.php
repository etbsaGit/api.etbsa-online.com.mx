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
        Schema::table('inv_items', function (Blueprint $table) {
            $table->unsignedBigInteger('shipping_status')->change()->nullable();
                $table->foreign('shipping_status')->references('id')->on('estatus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inv_items', function (Blueprint $table) {
            $table->tinyInteger('shipping_status')->change()->nullable();
            $table->dropForeign(['shipping_status']);
        });
    }
};
