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
        Schema::create('product_extras', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('precio_unidad',12,2);
            $table->unsignedBigInteger('currency_id');
                $table->foreign('currency_id')->references('id')->on('currency');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_extras');
    }
};
