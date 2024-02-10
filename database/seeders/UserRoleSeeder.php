<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Encuentra un usuario existente
        $user = User::where('email', 'admin@etbsa.com.mx')->first();

        $user->syncRoles('Admin');
    }
}

