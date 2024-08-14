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
        Schema::create('technicians_invoices', function (Blueprint $table) {
            $table->id();

            $table->string('folio')->unique();
            $table->decimal('cantidad', 10, 2);
            $table->date('fecha');
            $table->integer('horas_facturadas');
            $table->string('comentarios')->nullable();

            $table->unsignedBigInteger('tecnico_id');
            $table->foreign('tecnico_id')->references('id')->on('empleados')->onDelete('restrict');

            $table->unsignedBigInteger('wo_id');
            $table->foreign('wo_id')->references('id')->on('work_orders')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technicians_invoices');
    }
};
