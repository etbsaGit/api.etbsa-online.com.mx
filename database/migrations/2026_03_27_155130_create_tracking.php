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
        Schema::create('tracking', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('folio');
            $table->unsignedBigInteger('cliente_id');
                $table->foreign('cliente_id')->references('id')->on('clientes');
            $table->unsignedBigInteger('origen_track_id');
                $table->foreign('origen_track_id')->references('id')->on('tracking_origen')->onDelete('restrict');
            $table->unsignedBigInteger('vendedor_id');
                $table->foreign('vendedor_id')->references('id')->on('empleados')->onDelete('restrict');
            $table->unsignedBigInteger('sucursal_id');
                $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('restrict');
            $table->unsignedBigInteger('depto_id');
                $table->foreign('depto_id')->references('id')->on('tracking_depto')->onDelete('restrict');
            $table->unsignedBigInteger('estatus_id');
                $table->foreign('estatus_id')->references('id')->on('estatus')->onDelete('restrict');
            $table->unsignedBigInteger('certeza_id');
                $table->foreign('certeza_id')->references('id')->on('tracking_certeza')->onDelete('restrict');
            $table->unsignedBigInteger('category_id');
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('restrict');
            $table->unsignedBigInteger('condicion_pago_id');
                $table->foreign('condicion_pago_id')->references('id')->on('products_condicion_pago')->onDelete('restrict');
            $table->unsignedBigInteger('currency_id');
                $table->foreign('currency_id')->references('id')->on('currency')->onDelete('restrict');
            $table->decimal('subtotal',12,2);
            $table->unsignedBigInteger('iva_monto');
            $table->tinyInteger('incluye_iva');
            $table->decimal('tarifa_cambio',10,2);
            $table->decimal('descuento',10,2)->nullable();
            $table->decimal('total',10,2);
            $table->string('factura')->nullable();
            $table->date('date_next_tracking')->nullable();
            $table->date('date_lost_sale')->nullable();
            $table->date('date_won_sale')->nullable();
            $table->date('date_factura')->nullable();
            $table->date('date_delivery')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking');
    }
};
