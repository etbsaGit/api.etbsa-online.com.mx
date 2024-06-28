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
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->integer('ot')->unique();
            $table->string('cliente');
            $table->string('maquina');
            $table->string('descripcion');
            $table->date('fecha_ingreso');
            $table->date('fecha_entrega');
            $table->decimal('mano_obra', 10, 2)->default(0.00);
            $table->decimal('refacciones', 10, 2)->default(0.00);

            $table->unsignedBigInteger('tecnico_id')->nullable();
            $table->foreign('tecnico_id')->references('id')->on('empleados')->onDelete('restrict');

            $table->unsignedBigInteger('estatus_id')->nullable();
            $table->foreign('estatus_id')->references('id')->on('estatus')->onDelete('restrict');

            $table->unsignedBigInteger('estatus_taller_id')->nullable();
            $table->foreign('estatus_taller_id')->references('id')->on('estatus')->onDelete('restrict');

            $table->unsignedBigInteger('type_id')->nullable();
            $table->foreign('type_id')->references('id')->on('estatus')->onDelete('restrict');

            $table->unsignedBigInteger('bay_id')->nullable();
            $table->foreign('bay_id')->references('id')->on('bays')->onDelete('restrict');

            $table->unsignedBigInteger('sucursal_id')->nullable();
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('restrict');

            $table->unsignedBigInteger('linea_id')->nullable();
            $table->foreign('linea_id')->references('id')->on('lineas')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
