<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('credito_lineas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        DB::table('credito_lineas')->insert([
            [
                'name' => 'Maquinaria',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Refacciones',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Servicio',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Riego',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Lubricantes',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credito_lineas');
    }
};
