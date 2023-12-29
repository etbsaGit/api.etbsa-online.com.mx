<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $credentials = [
            'email' => $request->email,
            'password' => $request->password
        ];

        if (Auth::attempt($credentials)) {
            // $tokenp = Auth::user()->createToken('myapptoken')->plainTextToken;

            // $token = array('token' => $tokenp);
            //$user = User::find(1)->load('Empleado')

            // return response()->json($token);
            $user = User::where('email',$request->email)->first()->load('Empleado');
            return response()->json([
                'status' => true,
                'message' => 'Usuario logueado con exito',
                'data' => $user,
                'token' => $user->createToken('myapptoken')->plainTextToken
            ]);
        }
        return response()->json("Usuario y/o contraseña inválido", error: 401);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json('Logout exitoso');
    }

}