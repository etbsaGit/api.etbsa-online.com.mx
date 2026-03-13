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
        Schema::create('validate_sale_tractor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_tractor_id');
            $table->foreign('sale_tractor_id')->references('id')->on('sales_tractors')->onDelete('restrict');
            $table->boolean('is_valid')->default(false);
            $table->unsignedBigInteger('validated_by');
            $table->foreign('validated_by')->references('id')->on('empleados');
            $table->string('comments');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('validate_sale_tractor');
    }
};
