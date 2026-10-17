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
        Schema::create('vehicles_detalles_servicio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_servicio_id')->constrained('vehicles_ordenes_servicio');
            $table->foreignId('servicio_id')->constrained('vehicles_servicios_cat');
            $table->bigInteger('km_evento')->nullable();
            $table->text('descripcion');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('iva', 10, 2);
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles_detalles_servicio');
    }
};
