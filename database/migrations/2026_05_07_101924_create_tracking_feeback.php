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
        Schema::create('tracking_feedback', function (Blueprint $table) {
            $table->id();
            $table->string('comentario')->nullable();
            $table->unsignedBigInteger('empleado_id')->nullable();
                $table->foreign('empleado_id')->references('id')->on('empleados');
            $table->unsignedBigInteger('tracking_id');
                $table->foreign('tracking_id')->references('id')->on('tracking');
            $table->unsignedBigInteger('situacion_id');
                $table->foreign('situacion_id')->references('id')->on('estatus');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_feeback');
    }
};
