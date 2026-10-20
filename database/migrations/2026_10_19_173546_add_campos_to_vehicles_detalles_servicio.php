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
        Schema::table('vehicles_detalles_servicio', function (Blueprint $table) {
            $table->decimal('precio_unitario', 10, 2)->nullable();
            $table->integer('cantidad')->nullable();
            $table->foreignId('vehicle_id')->constrained('vehicles');
            $table->integer('llanta_anchura')->nullable();
            $table->integer('llanta_altura')->nullable();
            $table->integer('llanta_rin')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles_detalles_servicio', function (Blueprint $table) {
            $table->dropColumn('precio_unitario');
            $table->dropColumn('cantidad');
            $table->dropForeign(['vehicle_id']);
            $table->dropColumn('vehicle_id');
            $table->dropColumn('llanta_anchura');
            $table->dropColumn('llanta_altura');
            $table->dropColumn('llanta_rin');
        });
    }
};
