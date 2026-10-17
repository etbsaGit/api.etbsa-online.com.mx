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
        Schema::create('aux_vehicles_servicios', function (Blueprint $table) {
            $table->id();
            $table->date('fecha')->nullable();
            $table->string('orden_compra')->nullable();
            $table->string('n_aut_compras')->nullable();
            $table->string('tipo_servicio')->nullable();
            $table->string('servicio')->nullable();
            $table->string('suc_cargo')->nullable();
            $table->string('depto_cargo')->nullable();
            $table->string('empleado_id')->nullable();
            $table->string('placas_act')->nullable();
            $table->string('km_evento')->nullable();
            $table->string('proveedor')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('subtotal')->nullable();
            $table->string('iva')->nullable();
            $table->string('total')->nullable();
            $table->string('factura')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aux_vehicles_servicios');
    }
};
