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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();

            $table->integer('equip')->nullable();
            $table->string('nombre');
            $table->enum('tipo', ['fisica', 'moral']);
            $table->string('rfc')->unique();
            $table->string('curp')->nullable()->unique();
            $table->string('telefono');
            $table->string('telefono_casa')->nullable();
            $table->string('email')->nullable();

            // ------------------------------------Domicilio------------------------------------------
            $table->unsignedBigInteger('state_entity_id')->nullable();
            $table->foreign('state_entity_id')->references('id')->on('state_entities')->onDelete('restrict');

            $table->unsignedBigInteger('town_id')->nullable();
            $table->foreign('town_id')->references('id')->on('towns')->onDelete('restrict');

            $table->string('colonia');
            $table->string('calle')->nullable();
            $table->integer('codigo_postal');

            // ------------------------------------Segmentacion del cliente------------------------------------------
            $table->unsignedBigInteger('classification_id')->nullable();
            $table->foreign('classification_id')->references('id')->on('classifications')->onDelete('restrict');

            $table->unsignedBigInteger('segmentation_id')->nullable();
            $table->foreign('segmentation_id')->references('id')->on('segmentations')->onDelete('restrict');

            $table->unsignedBigInteger('technological_capability_id')->nullable();
            $table->foreign('technological_capability_id')->references('id')->on('technological_capabilities')->onDelete('restrict');

            $table->unsignedBigInteger('tactic_id')->nullable();
            $table->foreign('tactic_id')->references('id')->on('tactics')->onDelete('restrict');

            $table->unsignedBigInteger('construction_classification_id')->nullable();
            $table->foreign('construction_classification_id')->references('id')->on('construction_classifications')->onDelete('restrict');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
