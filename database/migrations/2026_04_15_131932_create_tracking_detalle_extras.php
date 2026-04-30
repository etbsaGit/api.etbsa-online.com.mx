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
        Schema::create('tracking_detalle_extras', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tracking_id');
                $table->foreign('tracking_id')->references('id')->on('tracking');
            $table->unsignedBigInteger('extra_id');
                $table->foreign('extra_id')->references('id')->on('product_extras');
            $table->decimal('precio_unidad',12,2);
            $table->unsignedBigInteger('cantidad');
            $table->decimal('subtotal',12,2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_detalle_extras');
    }
};
