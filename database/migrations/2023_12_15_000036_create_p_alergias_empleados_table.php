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
        Schema::create('p_alergias_empleados', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('alergia_id')->nullable();
            $table->unsignedBigInteger('empleado_id')->nullable();

            $table->foreign('alergia_id')->references('id')->on('alergias')->onDelete('set null');
            $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('p_alergias_empleados');
    }
};
