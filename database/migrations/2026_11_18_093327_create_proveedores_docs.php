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
        Schema::create('proveedores_docs', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_doc');
            $table->string('url');
            $table->foreignId('estatus_id')->constrained('estatus');
            $table->string('notas')->nullable();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->foreignId('validated_by')->nullable()->constrained('users');
            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->date('fecha_vencimiento')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedores_docs');
    }
};
