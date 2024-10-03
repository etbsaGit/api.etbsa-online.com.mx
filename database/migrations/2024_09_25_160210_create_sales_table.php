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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            $table->decimal('amount', 10, 2)->default(0.00);
            $table->string('comments')->nullable();
            $table->string('serial')->nullable();
            $table->string('invoice')->nullable();
            $table->string('order')->nullable();
            $table->string('folio')->nullable();
            $table->string('economic')->nullable();
            $table->boolean('validated')->default(false);
            $table->date('date')->nullable();

            $table->unsignedBigInteger('cliente_id');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('restrict');

            $table->unsignedBigInteger('status_id');
            $table->foreign('status_id')->references('id')->on('estatus')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
