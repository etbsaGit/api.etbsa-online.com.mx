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
        Schema::create('tractor_contrapesos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->unsignedBigInteger('contrapeso_id');
                $table->foreign('contrapeso_id')->references('id')->on('contrapesos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tractor_contrapesos');
    }
};
