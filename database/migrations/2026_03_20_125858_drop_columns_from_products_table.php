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
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'price',
                'sale_price',
                'slug',
                'quantity',
                'featured'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal(12,2)('price');
            $table->decimal(12,2)('sale_price');
            $table->string('slug');
            $table->unsignedBigInteger('quanity');
            $table->tinyInteger('featured');
        });
    }
};
