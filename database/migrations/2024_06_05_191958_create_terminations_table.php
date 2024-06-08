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
        Schema::create('terminations', function (Blueprint $table) {
            $table->id();
            $table->string('reason');
            $table->string('comments');
            $table->date('date');
            $table->unsignedBigInteger('estatus_id')->nullable();
            $table->foreign('estatus_id')->references('id')->on('estatus')->onDelete('restrict');
            $table->unsignedBigInteger('empleado_id')->nullable();
            $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terminations');
    }
};
