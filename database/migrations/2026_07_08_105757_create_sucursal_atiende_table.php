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
        Schema::create('sucursal_atiende', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sucursal_id');
                $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('restrict');
            $table->unsignedBigInteger('town_id');
                $table->foreign('town_id')->references('id')->on('towns')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sucursal_atiende');
    }
};
