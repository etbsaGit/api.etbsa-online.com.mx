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
        Schema::create('desvinculaciones', function (Blueprint $table) {
            $table->id();

            $table->date('fecha');
            $table->string('comentarios');

            $table->unsignedBigInteger('tipo_de_desvinculacion_id')->nullable();

            $table->foreign('tipo_de_desvinculacion_id')->references('id')->on('tipos_de_desvinculaciones')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('desvinculaciones');
    }
};
