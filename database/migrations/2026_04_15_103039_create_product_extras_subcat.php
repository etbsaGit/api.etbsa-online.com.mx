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
        Schema::create('product_extras_subcat', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('extra_id');
                $table->foreign('extra_id')->references('id')->on('product_extras');
            $table->unsignedBigInteger('subcategory_id');
                $table->foreign('subcategory_id')->references('id')->on('product_subcategory');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_extras_subcat');
    }
};
