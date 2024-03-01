<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // User::create([
        //     'name'=>'aguayocesar',
        //     'email'=>'aguayocesar@etbsa.com.mx',
        //     'password'=>Hash::make('12345678')
        // ]);

        User::create([
            'name'=>'admin',
            'email'=>'admin@etbsa.com.mx',
            'password'=>Hash::make('12345678')
        ]);

        // for ($i = 1; $i < 10; $i++) {
        //     User::create(
        //         [
        //             'name' => "usuario$i",
        //             'email' => "usuario$i@ejemplo.com",
        //             'email_verified_at' => now(),
        //             'password' => Hash::make('12345678'),
        //             'remember_token' => Str::random(10),
        //             'created_at' => now(),
        //             'updated_at' => now(),
        //         ]
        //     );
        // }
    }
}
