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
            $table->tinyInteger('is_usado');
            $table->tinyInteger('is_dollar');
            $table->double('price_1')->nullable();
            $table->double('price_2')->nullable();
            $table->double('price_4')->nullable();
            $table->double('price_5')->nullable();
            $table->double('price_6')->nullable();
            $table->double('price_7')->nullable();
            $table->double('price_8')->nullable();
            $table->double('price_9')->nullable();
            $table->double('price_10')->nullable();
            $table->double('price_11')->nullable();
            $table->double('price_12')->nullable();
            $table->double('price_13')->nullable();
            $table->double('price_14')->nullable();
            $table->unsignedBigInteger('product_category_id');
            $table->unsignedBigInteger('product_subcategory_id');
            $table->unsignedBigInteger('currency_id');
            $table->unsignedBigInteger('agency_id');

            $table->foreign('product_category_id')->references('id')->on('categories');
            $table->foreign('product_subcategory_id')->references('id')->on('product_subcategory');
            $table->foreign('currency_id')->references('id')->on('currency');
            $table->foreign('agency_id')->references('id')->on('sucursales');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['product_category_id']);
            $table->dropForeign(['product_subcategory_id']);
            $table->dropForeign(['currency_id']);
            $table->dropForeign(['agency_id']);
            $table->dropColumn([
                'is_usado',
                'is_dollar',
                'price_1',
                'price_2',
                'price_4',
                'price_5',
                'price_6',
                'price_7',
                'price_8',
                'price_9',
                'price_10',
                'price_11',
                'price_12',
                'price_13',
                'price_14',
                'product_category_id',
                'product_subcategory_id',
                'currency_id',
                'agency_id'
            ]);
        });
    }
};
