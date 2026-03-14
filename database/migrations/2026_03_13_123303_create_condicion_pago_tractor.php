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
        Schema::create('condicion_pago_tractor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_tractor_id');
            $table->foreign('sale_tractor_id')->references('id')->on('sales_tractors')->onDelete('restrict');
            $table->decimal('enganche', 10, 2);
            $table->decimal('monto_financiado', 10, 2)->nullable();
            $table->integer('plazo_meses')->nullable();
            $table->string('comments')->nullable();
            $table->decimal('pago_periodo',10,2)->nullable();
            $table->date('fecha_primer_pago')->nullable();
            $table->unsignedBigInteger('estatus_id')->nullable();
            $table->foreign('estatus_id')->references('id')->on('estatus')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('condicion_pago_tractor');
    }
};
