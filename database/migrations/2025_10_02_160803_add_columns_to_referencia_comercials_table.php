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
        Schema::table('referencia_comercials', function (Blueprint $table) {
            $table->string('negocio');
            $table->string('domicilio');
            $table->string('empresa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('referencia_comercials', function (Blueprint $table) {
            //
        });
    }
};
