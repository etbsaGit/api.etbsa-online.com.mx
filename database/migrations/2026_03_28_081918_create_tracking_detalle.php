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
        Schema::create('tracking_detalle', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tracking_id');
                $table->foreign('tracking_id')->references('id')->on('tracking');
            $table->unsignedBigInteger('product_id');
                $table->foreign('product_id')->references('id')->on('products');
            $table->unsignedBigInteger('cantidad');
            $table->decimal('precio_unidad',12,2);
            $table->decimal('subtotal',12,2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_detalle');
    }
};
