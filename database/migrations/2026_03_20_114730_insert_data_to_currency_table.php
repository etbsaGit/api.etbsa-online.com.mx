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
        DB::table('currency')->insert([
            [
                'name' => 'MXN',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'USD',
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('currency')->whereIn('name', ['MXN', 'USD'])->delete();
    }
};
