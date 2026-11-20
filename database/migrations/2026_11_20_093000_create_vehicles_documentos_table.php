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
        Schema::create('vehicles_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->string('tipo_documento', 50); // seguro, tarjeta_circulacion, verificacion
            $table->string('folio_o_poliza')->nullable();
            $table->string('detalle_extra')->nullable(); // Aseguradora, Entidad/Estado o Holograma/Periodo
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento')->nullable(); // NULL si es permanente (ej. tarjeta de circulación sin vencimiento)
            $table->string('archivo_path')->nullable();
            $table->boolean('es_vigente')->default(true);
            $table->text('observaciones')->nullable();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['vehicle_id', 'tipo_documento', 'es_vigente'], 'idx_vehicle_doc_vigente');
            $table->index('fecha_vencimiento', 'idx_vehicle_doc_vencimiento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles_documentos');
    }
};
