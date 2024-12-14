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
        Schema::create('rental_periods', function (Blueprint $table) {
            $table->id();
            $table->string('folio');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('billing_day')->unsigned();
            $table->decimal('rental_value', 10, 2);
            $table->text('comments')->nullable();
            $table->string('document')->nullable();

            $table->unsignedBigInteger('empleado_id')->nullable();
            $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('restrict');

            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('restrict');

            $table->unsignedBigInteger('rental_machine_id')->nullable();
            $table->foreign('rental_machine_id')->references('id')->on('rental_machines')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_periods');
    }
};
