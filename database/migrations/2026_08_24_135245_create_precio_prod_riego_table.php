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
        Schema::create('precio_prod_riego', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('products_riego');
            $table->decimal('precio', 10, 2);
            $table->foreignId('nivel_partner_id')->constrained('nivel_partner');
            $table->foreignId('currency_id')->constrained('currency');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('precio_prod_riego');
    }
};
