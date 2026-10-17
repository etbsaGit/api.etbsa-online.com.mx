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
        Schema::create('vehicles_ordenes_servicio', function (Blueprint $table) {
            $table->id();
            $table->string('autorizacion_compras');
            $table->string('orden_compra')->nullable();
            $table->date('fecha_elaboracion');
            $table->foreignId('vehicle_id')->constrained('vehicles');
            $table->foreignId('proveedor_id')->contrainted('vehicles_servicio_proveedores');
            $table->foreignId('solicitante_id')->constrained('empleados')->nullable();
            $table->foreignId('suc_cargo_id')->constrained('vehicles_suc_cargo_a')->nullable();
            $table->foreignId('depto_cargo_id')->constrained('vehicles_depto_cargo_a')->nullable();
            $table->string('factura')->nullable();
            $table->string('factura_url')->nullable();
            $table->string('orden_compra_url')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles_ordenes_servicio');
    }
};
