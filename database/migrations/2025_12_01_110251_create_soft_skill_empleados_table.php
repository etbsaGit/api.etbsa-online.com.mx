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
        Schema::create('soft_skill_empleados', function (Blueprint $table) {
            $table->id();

            $table->string('definicion');
            $table->string('evidencia');

            $table->unsignedBigInteger('empleado_id')->nullable();
            $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('restrict');

            $table->unsignedBigInteger('soft_skill_id')->nullable();
            $table->foreign('soft_skill_id')->references('id')->on('soft_skills')->onDelete('restrict');

            $table->unsignedBigInteger('soft_skill_nivel_id')->nullable();
            $table->foreign('soft_skill_nivel_id')->references('id')->on('soft_skill_niveles')->onDelete('restrict');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soft_skill_empleados');
    }
};
