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
        Schema::create('vacation_dia_cuenta_bitacora', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->nullable()->constrained('empleados');
            $table->foreignId('dia_cuenta_id')->nullable()->constrained('vacation_dia_cuenta');
            $table->foreignId('vacation_day_id')->nullable()->constrained('vacation_days')->nullOnDelete();
            $table->foreignId('estatus_id')->nullable()->constrained('estatus');
            $table->text('comentario')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacation_dia_cuenta_bitacora');
    }
};
