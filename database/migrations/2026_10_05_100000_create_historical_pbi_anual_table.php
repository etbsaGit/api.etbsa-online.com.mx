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
        Schema::create('historical_pbi_anual', function (Blueprint $table) {
            $table->id();
            $table->string('clave_cliente', 100)->nullable()->index();
            $table->string('rfc', 50)->nullable();
            $table->string('nombre_cliente', 255)->nullable()->index();
            $table->string('nom_clie_equip', 255)->nullable()->index();
            $table->integer('anio')->nullable()->index();
            $table->string('id_sucursal', 50)->nullable();
            $table->string('sucursal', 100)->nullable();
            $table->string('nip', 100)->nullable();
            
            // Ventas
            $table->decimal('venta_maquinaria', 16, 2)->default(0);
            $table->decimal('venta_refacciones', 16, 2)->default(0);
            $table->decimal('venta_servicio', 16, 2)->default(0);
            $table->decimal('venta_riego', 16, 2)->default(0);
            $table->decimal('venta_chevron', 16, 2)->default(0);
            $table->decimal('venta_nuevas_tecnologias', 16, 2)->default(0);
            $table->decimal('total_venta', 16, 2)->default(0);

            // Costos
            $table->decimal('costo_maquinaria', 16, 2)->default(0);
            $table->decimal('costo_refacciones', 16, 2)->default(0);
            $table->decimal('costo_servicio', 16, 2)->default(0);
            $table->decimal('costo_riego', 16, 2)->default(0);
            $table->decimal('costo_chevron', 16, 2)->default(0);
            $table->decimal('costo_nuevas_tecnologias', 16, 2)->default(0);
            $table->decimal('total_costo', 16, 2)->default(0);

            // Márgenes
            $table->decimal('margen_maquinaria', 16, 2)->default(0);
            $table->decimal('margen_refacciones', 16, 2)->default(0);
            $table->decimal('margen_servicio', 16, 2)->default(0);
            $table->decimal('margen_riego', 16, 2)->default(0);
            $table->decimal('margen_chevron', 16, 2)->default(0);
            $table->decimal('margen_nuevas_tecnologias', 16, 2)->default(0);
            $table->decimal('margen_total', 16, 2)->default(0);

            // % Márgenes
            $table->decimal('pct_margen_maquinaria', 16, 2)->default(0);
            $table->decimal('pct_margen_refacciones', 16, 2)->default(0);
            $table->decimal('pct_margen_servicio', 16, 2)->default(0);
            $table->decimal('pct_margen_riego', 16, 2)->default(0);
            $table->decimal('pct_margen_chevron', 16, 2)->default(0);
            $table->decimal('pct_margen_nuevas_tecnologias', 16, 2)->default(0);
            $table->decimal('pct_margen_total', 16, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historical_pbi_anual');
    }
};
