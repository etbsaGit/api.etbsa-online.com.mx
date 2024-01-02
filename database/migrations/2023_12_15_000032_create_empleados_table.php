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

            $table->string('nombre');
            $table->string('segundoNombre')->nullable();
            $table->string('apellidoPaterno');
            $table->string('apellidoMaterno');
            $table->string('telefono');
            $table->date('fechaDeNacimiento');
            $table->string('curp');
            $table->string('rfc');
            $table->string('ine');
            $table->string('pasaporte')->nullable();
            $table->bigInteger('visa')->nullable();
            $table->string('licenciaDeManejo')->nullable();
            $table->bigInteger('nss');
            $table->date('fechaDeIngreso');
            $table->integer('hijos')->nullable();
            $table->string('dependientesEconomicos')->nullable();
            $table->string('cedulaProfesional')->nullable();
            $table->boolean('matriz');
            $table->integer('sueldoBase');
            $table->boolean('comision');
            $table->string('foto')->nullable();
            $table->string('numeroExterior');
            $table->string('numeroInterior')->nullable();
            $table->string('calle');
            $table->string('colonia');
            $table->integer('codigoPostal');
            $table->string('ciudad');
            $table->string('estado');
            $table->string('cuentaBancaria');
            $table->string('constelacionFamiliar')->nullable();
            $table->STRING('status');

            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('puesto_id')->nullable();
            $table->unsignedBigInteger('sucursal_id')->nullable();
            $table->unsignedBigInteger('linea_id')->nullable();
            $table->unsignedBigInteger('departamento_id')->nullable();
            $table->unsignedBigInteger('estadoCivil_id')->nullable();
            $table->unsignedBigInteger('tipoDeSangre_id')->nullable();
            $table->unsignedBigInteger('expediente_id')->nullable();
            $table->unsignedBigInteger('desvinculacion_id')->nullable();
            $table->unsignedBigInteger('escolaridad_id')->nullable();

            $table->foreign('escolaridad_id')->references('id')->on('escolaridades')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('puesto_id')->references('id')->on('puestos')->onDelete('set null');
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('set null');
            $table->foreign('linea_id')->references('id')->on('lineas')->onDelete('set null');
            $table->foreign('departamento_id')->references('id')->on('departamentos')->onDelete('set null');
            $table->foreign('estadoCivil_id')->references('id')->on('estados_civiles')->onDelete('set null');
            $table->foreign('tipoDeSangre_id')->references('id')->on('tipos_de_sangre')->onDelete('set null');
            $table->foreign('expediente_id')->references('id')->on('expedientes')->onDelete('set null');
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
