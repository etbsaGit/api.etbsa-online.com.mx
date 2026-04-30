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
            $table->dropForeign(['certeza_id']);
            $table->dropColumn('certeza_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::table('tracking', function (Blueprint $table) {
        $table->unsignedBigInteger('certeza_id')->nullable();

        $table->foreign('certeza_id')
              ->references('id')
              ->on('tracking_certeza')
              ->onDelete('set null');
    });
    }
};
