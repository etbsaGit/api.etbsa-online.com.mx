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
        Schema::create('technicians_logs', function (Blueprint $table) {
            $table->id();

            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_termino');
            $table->string('comentarios')->nullable();

            $table->unsignedBigInteger('tecnico_id');
            $table->foreign('tecnico_id')->references('id')->on('empleados')->onDelete('restrict');

            $table->unsignedBigInteger('wo_id');
            $table->foreign('wo_id')->references('id')->on('work_orders')->onDelete('restrict');

            $table->unsignedBigInteger('activity_technician_id');
            $table->foreign('activity_technician_id')->references('id')->on('activity_technicians')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technicians_logs');
    }
};
