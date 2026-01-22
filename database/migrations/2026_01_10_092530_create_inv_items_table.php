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
        Schema::create('inv_items', function (Blueprint $table) {
            $table->id();
            $table->string('factory');
            $table->string('rd');
            $table->date('shipping_date');
            $table->string('invoice')->nullable();
            $table->string('s_n')->nullable();
            $table->string('s_n_m')->nullable();
            $table->string('e_n')->nullable();
            $table->string('financing')->nullable();
            $table->date('invoice_date')->nullable();
            $table->decimal('purchase_cost', 8, 2)->nullable();
            $table->boolean('is_paid')->nullable();
            $table->boolean('gps')->nullable();

            $table->string('notes')->nullable();

            $table->unsignedBigInteger('inv_model_id')->nullable();
            $table->foreign('inv_model_id')->references('id')->on('inv_models')->onDelete('restrict');

            $table->unsignedBigInteger('tipo_equipo_id')->nullable();
            $table->foreign('tipo_equipo_id')->references('id')->on('tipos_equipo')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_items');
    }
};
