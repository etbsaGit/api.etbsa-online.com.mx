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
            $table->unsignedBigInteger('notificar_a')->after('sucursal_id')->nullable();
                $table->foreign('notificar_a')->references('id')->on('empleados');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tracking', function (Blueprint $table) {
            $table->dropColumn('notificar_a');
        });
    }
};
