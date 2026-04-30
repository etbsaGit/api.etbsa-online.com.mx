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
            $table->dropForeign('tracking_detalle_extras_extra_id_foreign');
            $table->foreign('extra_id')->references('id')->on('contrapesos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tracking_detalle_extras', function (Blueprint $table) {
            $table->dropForeign(['extra_id']); // elimina la FK actual

            $table->foreign('extra_id')
                ->references('id')
                ->on('tracking_detalle_extras');
        });
    }
};
