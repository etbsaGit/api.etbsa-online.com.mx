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
        Schema::create('visits', function (Blueprint $table) {
            $table->id();

            $table->date('dia');
            $table->string('cliente');
            $table->string('ubicacion');
            $table->string('telefono')->nullable();
            $table->string('cultivos')->nullable();
            $table->string('hectareas')->nullable();
            $table->string('maquinaria')->nullable();
            $table->string('comentarios')->nullable();
            $table->string('retroalimentacion')->nullable();

            $table->unsignedBigInteger('empleado_id');
            $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
