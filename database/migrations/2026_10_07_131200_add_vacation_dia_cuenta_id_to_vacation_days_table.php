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
        Schema::table('vacation_days', function (Blueprint $table) {
            $table->unsignedBigInteger('vacation_dia_cuenta_id')->nullable()->after('cubre');
            $table->foreign('vacation_dia_cuenta_id')
                ->references('id')
                ->on('vacation_dia_cuenta')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vacation_days', function (Blueprint $table) {
            $table->dropForeign(['vacation_dia_cuenta_id']);
            $table->dropColumn('vacation_dia_cuenta_id');
        });
    }
};
