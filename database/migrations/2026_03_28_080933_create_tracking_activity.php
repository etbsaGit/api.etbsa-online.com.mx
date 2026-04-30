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
        Schema::create('tracking_activity', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tracking_id');
                $table->foreign('tracking_id')->references('id')->on('tracking');
            $table->unsignedBigInteger('tipo_seguimiento_id');
                $table->foreign('tipo_seguimiento_id')->references('id')->on('tracking_tipo_seguimiento');
            $table->unsignedBigInteger('certeza_id');
                $table->foreign('certeza_id')->references('id')->on('tracking_certeza');
            $table->decimal('ultimo_precio_tratar',12,2);
            $table->decimal('tarifa_cambio',10,2);
            $table->unsignedBigInteger('currency_id');
                $table->foreign('currency_id')->references('id')->on('currency');
            $table->string('notas')->nullable();
            $table->date('date_next_tracking');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_activity');
    }
};
