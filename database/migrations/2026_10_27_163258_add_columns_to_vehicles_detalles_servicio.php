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
            $table->foreignId('forma_pago_id')->nullable()->constrained('cat_formas_pago');
            $table->foreignId('metodo_pago_id')->nullable()->constrained('cat_metodos_pago');
            $table->foreignId('uso_cfdi_id')->nullable()->constrained('cat_usos_cfdi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles_detalles_servicio', function (Blueprint $table) {
            $table->dropForeign(['forma_pago_id']);
            $table->dropForeign(['metodo_pago_id']);
            $table->dropForeign(['uso_cfdi_id']);
            $table->dropColumn(['forma_pago_id', 'metodo_pago_id', 'uso_cfdi_id']);
        });
    }
};
