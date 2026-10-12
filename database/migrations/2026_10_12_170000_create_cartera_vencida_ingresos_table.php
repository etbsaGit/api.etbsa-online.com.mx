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
        Schema::create('cartera_vencida_ingresos', function (Blueprint $table) {
            $table->id();
            $table->string('id_cliente', 100)->nullable()->index(); // Col 1 ID_Cliente (equip)
            $table->string('v_rec_no', 100)->nullable();
            $table->string('folio', 100)->nullable()->index();       // Col 2 v_folio
            $table->string('fecha_movimiento', 50)->nullable();     // Col 3 FECHA MOVIMIENTO
            $table->integer('ano')->nullable()->index();             // Col 50 AÑO
            $table->string('mes', 50)->nullable()->index();          // Col 49 MES
            $table->integer('dia')->nullable();                      // Día parseado
            $table->date('fecha_orden')->nullable()->index();        // Para ordenamiento cronológico
            $table->text('descripcion')->nullable();                 // Col 20 Descripcion
            
            // Columnas de montos
            $table->decimal('credito', 16, 2)->default(0);           // Col 35 CREDITO
            $table->decimal('contado', 16, 2)->default(0);           // Col 36 CONTADO
            $table->decimal('nota_debito', 16, 2)->default(0);       // Col 37 NOTA DEBITO
            $table->decimal('nota_credito', 16, 2)->default(0);      // Col 38 NOTA CREDITO
            $table->decimal('pagos_cobranza', 16, 2)->default(0);    // Col 39 PAGOS COBRANZA
            $table->decimal('pagos_mostrador', 16, 2)->default(0);   // Col 40 PAGOS MOSTRADOR
            
            // Campos auxiliares
            $table->string('sucursal', 150)->nullable();             // Col 46 Sucursal
            $table->string('condiciones', 100)->nullable();          // Col 15 Condiciones
            $table->string('tipo_moneda', 50)->nullable();           // Col 22 v_tipo_moneda2
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cartera_vencida_ingresos');
    }
};
