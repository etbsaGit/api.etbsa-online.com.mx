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
        Schema::create('tracking_asignacion_serie', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tracking_id');
                $table->foreign('tracking_id')->references('id')->on('tracking');
            $table->unsignedBigInteger('inv_item_id');
                $table->foreign('inv_item_id')->references('id')->on('inv_items');
            $table->unsignedBigInteger('asignado_por');
                $table->foreign('asignado_por')->references('id')->on('empleados');
            $table->string('comentarios')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_asignacion_serie');
    }
};
