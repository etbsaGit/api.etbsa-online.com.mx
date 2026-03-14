<?php

namespace Database\Seeders;

use App\Models\Intranet\SaleTractor;
use Illuminate\Database\Seeder;

class SaleTractorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SaleTractor::factory()->count(50)->create();
    }
}
