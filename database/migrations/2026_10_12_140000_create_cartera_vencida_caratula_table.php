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
        Schema::create('cartera_vencida_caratula', function (Blueprint $table) {
            $table->id();
            $table->string('id_cliente', 100)->nullable()->index(); // Col X (equip)
            $table->string('cliente', 255)->nullable()->index();    // Col N
            $table->string('depto', 150)->nullable();                // Col H (Departamento / Modulo)
            $table->string('folio', 100)->nullable()->index();       // Col AD
            $table->string('fecha_vencimiento', 50)->nullable();     // Col Y
            
            // Ubicación y conceptos
            $table->string('id_sucursal', 50)->nullable();           // Col A
            $table->string('sucursal', 150)->nullable();             // Col B
            $table->string('linea', 150)->nullable();                // Col C
            $table->string('concepto', 150)->nullable();             // Col D
            $table->string('serie_fiscal', 50)->nullable();          // Col I
            
            // Antigüedad de saldos
            $table->decimal('mas_de_90', 16, 2)->default(0);         // Col O
            $table->decimal('mas_de_60', 16, 2)->default(0);         // Col P
            $table->decimal('mas_de_30', 16, 2)->default(0);         // Col Q
            $table->decimal('mas_de_15', 16, 2)->default(0);         // Col R
            $table->decimal('de_1_a_15', 16, 2)->default(0);         // Col S (Entre 1 y 15 dias)
            $table->decimal('por_vencer', 16, 2)->default(0);        // Col T
            $table->decimal('total', 16, 2)->default(0);             // Col U (Cartera Total)
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cartera_vencida_caratula');
    }
};
