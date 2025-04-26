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
        Schema::create('caja_denominaciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // moneda o billete
            $table->decimal('valor', 8, 2); // Valor de la denominación, ej. 20.00
            $table->string('tipo'); // moneda o billete

            $table->unique(['valor', 'tipo']); // combinación única

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caja_denominaciones');
    }
};
