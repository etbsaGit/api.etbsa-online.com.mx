<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ChangePasswordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            // Verificar si el usuario tiene un empleado asociado y si tiene un RFC
            if ($user->empleado && $user->empleado->rfc) {
                // Obtener el RFC del empleado asociado al usuario
                $rfc = $user->empleado->rfc;

                // Cambiar la contraseña del usuario por el RFC
                $user->password = Hash::make($rfc);
                $user->save();
            } else {
                // Asignar una contraseña predeterminada si el usuario no tiene RFC
                $user->password = Hash::make('password123');
                $user->save();
            }
        }
    }
}
