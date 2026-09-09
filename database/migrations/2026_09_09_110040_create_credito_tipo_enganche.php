<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('credito_tipo_enganche', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->timestamps();
        });
        DB::table('credito_tipo_enganche')->insert([
            'nombre' => 'Anticipo',
        ]);
        DB::table('credito_tipo_enganche')->insert([
            'nombre' => 'Sin Anticipo',
        ]);
        DB::table('credito_tipo_enganche')->insert([
            'nombre' => 'A Cuenta',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credito_tipo_enganche');
    }
};
