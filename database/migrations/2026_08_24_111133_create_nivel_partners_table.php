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
        Schema::create('nivel_partner', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        DB::table('nivel_partner')->insert([
            [
                'name' => 'Bronce',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Plata',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Oro',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Platino',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Diamante',
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
        Schema::dropIfExists('nivel_partner');
    }
};
