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
        Schema::create('historical_pbi_ventas', function (Blueprint $table) {
            $table->id();
            $table->string('fecha_factura', 50)->nullable();
            $table->string('id_sucursal', 50)->nullable();
            $table->string('sucursal', 100)->nullable()->index();
            $table->string('tipo_venta', 50)->nullable();
            $table->string('departamento', 100)->nullable()->index();
            $table->string('clasificacion', 100)->nullable()->index();
            $table->string('descripcion_corta', 150)->nullable();
            $table->string('categoria', 100)->nullable()->index();
            $table->string('depto_vendedor', 100)->nullable();
            $table->string('nip', 100)->nullable()->index();
            $table->string('modelo', 100)->nullable()->index();
            $table->string('descripcion_producto', 255)->nullable();
            $table->string('marca', 100)->nullable();
            $table->string('clave_vendedor', 100)->nullable();
            $table->string('clave_cliente', 100)->nullable()->index();
            $table->string('nombre_vendedor', 150)->nullable();
            $table->string('nombre_cliente', 255)->nullable()->index();
            $table->string('estado', 100)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('cp', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('estatus_vendedor', 50)->nullable();
            $table->decimal('precio_venta', 16, 2)->default(0);
            $table->decimal('total_costo', 16, 2)->default(0);
            $table->decimal('margen', 16, 2)->default(0);
            $table->decimal('margen_pct', 8, 2)->default(0);
            $table->decimal('total', 16, 2)->default(0);
            $table->integer('mes_no')->nullable();
            $table->string('mes', 50)->nullable();
            $table->integer('anio')->nullable()->index();
            $table->string('ciclo_anio', 50)->nullable();
            $table->string('ultima_compra', 50)->nullable();
            $table->string('zona_sucursal', 100)->nullable();
            $table->string('semestre', 50)->nullable();
            $table->string('trimestre', 50)->nullable();
            $table->string('cat_seminuevos', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historical_pbi_ventas');
    }
};
