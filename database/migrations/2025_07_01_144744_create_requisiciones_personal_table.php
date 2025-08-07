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
        Schema::create('requisicion_personals', function (Blueprint $table) {
            $table->id();

            // Generales del puesto
            $table->string('sexo')->nullable();
            $table->string('rango_edad')->nullable();
            $table->text('habilidades')->nullable();
            $table->string('idiomas')->nullable();
            $table->string('manejo_equipo')->nullable();
            $table->decimal('sueldo_mensual_inicial', 10, 2)->nullable();
            $table->decimal('comisiones', 10, 2)->nullable();
            $table->text('experiencia_conocimientos')->nullable();
            $table->text('actividades_desempenar')->nullable();

            $table->integer('total_posiciones')->nullable();

            $table->enum('tipo_vacante', ['Remplazo', 'Nueva Creación', 'Temporal', 'Permanente'])->nullable();
            $table->string('motivo_vacante')->nullable();
            $table->string('especificar_vacante')->nullable();

            $table->unsignedBigInteger('puesto_id')->nullable();
            $table->foreign('puesto_id')->references('id')->on('puestos')->onDelete('set null');

            $table->unsignedBigInteger('sucursal_id')->nullable();
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('set null');

            $table->unsignedBigInteger('linea_id')->nullable();
            $table->foreign('linea_id')->references('id')->on('lineas')->onDelete('set null');

            $table->unsignedBigInteger('departamento_id')->nullable();
            $table->foreign('departamento_id')->references('id')->on('departamentos')->onDelete('set null');

            $table->unsignedBigInteger('escolaridad_id')->nullable();
            $table->foreign('escolaridad_id')->references('id')->on('escolaridades')->onDelete('set null');

            $table->unsignedBigInteger('solicita_id')->nullable();
            $table->foreign('solicita_id')->references('id')->on('empleados')->onDelete('set null');

            $table->unsignedBigInteger('autoriza_id')->nullable();
            $table->foreign('autoriza_id')->references('id')->on('empleados')->onDelete('set null');

            $table->unsignedBigInteger('vo_bo_id')->nullable();
            $table->foreign('vo_bo_id')->references('id')->on('empleados')->onDelete('set null');

            $table->unsignedBigInteger('recibe_id')->nullable();
            $table->foreign('recibe_id')->references('id')->on('empleados')->onDelete('set null');

            $table->boolean('autorizacion')->nullable();
            $table->boolean('estatus')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisiciones_personals');
    }
};
