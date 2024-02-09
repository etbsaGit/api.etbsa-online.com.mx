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
        Schema::create('empleados', function(Blueprint $table){
            $table -> id();

            $table->string('fotografia')->nullable();
            $table->string('nombre');
            $table->string('segundo_nombre')->nullable();
            $table->string('apellido_paterno');
            $table->string('apellido_materno');
            $table->string('telefono')->nullable();
            $table->string('telefono_institucional')->nullable();
            $table->date('fecha_de_nacimiento')->nullable();
            $table->string('curp')->nullable();
            $table->string('rfc')->nullable();
            $table->string('ine')->nullable();
            $table->string('pasaporte')->nullable();
            $table->bigInteger('visa')->nullable();
            $table->string('licencia_de_manejo')->nullable();
            $table->bigInteger('nss')->nullable();
            $table->date('fecha_de_ingreso');
            $table->integer('hijos')->nullable();
            $table->string('dependientes_economicos')->nullable();
            $table->string('cedula_profesional')->nullable();
            $table->boolean('matriz')->default(false);
            $table->integer('sueldo_base')->nullable();
            $table->boolean('comision')->default(false);
            $table->string('numero_exterior')->nullable();
            $table->string('numero_interior')->nullable();
            $table->string('calle')->nullable();
            $table->string('colonia')->nullable();
            $table->integer('codigo_postal')->nullable();
            $table->string('ciudad')->nullable();
            $table->string('estado')->nullable();
            $table->string('cuenta_bancaria')->nullable();
            $table->string('constelacion_familiar')->nullable();
            $table->string('status')->nullable();
            $table->string('correo_institucional')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('puesto_id')->nullable();
            $table->unsignedBigInteger('sucursal_id')->nullable();
            $table->unsignedBigInteger('linea_id')->nullable();
            $table->unsignedBigInteger('departamento_id')->nullable();
            $table->unsignedBigInteger('estado_civil_id')->nullable();
            $table->unsignedBigInteger('tipo_de_sangre_id')->nullable();
            $table->unsignedBigInteger('desvinculacion_id')->nullable();
            $table->unsignedBigInteger('escolaridad_id')->nullable();
            $table->foreign('escolaridad_id')->references('id')->on('escolaridades')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('puesto_id')->references('id')->on('puestos')->onDelete('set null');
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('set null');
            $table->foreign('linea_id')->references('id')->on('lineas')->onDelete('set null');
            $table->foreign('departamento_id')->references('id')->on('departamentos')->onDelete('set null');
            $table->foreign('estado_civil_id')->references('id')->on('estados_civiles')->onDelete('set null');
            $table->foreign('tipo_de_sangre_id')->references('id')->on('tipos_de_sangre')->onDelete('set null');
            $table->foreign('desvinculacion_id')->references('id')->on('desvinculaciones')->onDelete('set null');

            $table->timestamps();
        });
    } 

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
