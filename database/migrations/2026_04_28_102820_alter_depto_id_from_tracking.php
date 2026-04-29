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
            $table->dropForeign('tracking_depto_id_foreign');
            $table->dropColumn('depto_id');
            $table->unsignedBigInteger('departamento_id')->nullable()->after('sucursal_id');
                $table->foreign('departamento_id')->references('id')->on('departamentos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tracking', function (Blueprint $table) {
            $table->dropColumn('departamento_id');
            $table->unsignedBigInteger('depto_id');
                $table->foreign('depto_id')->references('id')->on('tracking_depto');
        });
    }
};
