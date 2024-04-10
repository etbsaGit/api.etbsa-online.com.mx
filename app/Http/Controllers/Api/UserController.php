<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use App\Http\Requests\User\StoreRequest;
use App\Http\Requests\User\PutRequest;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends ApiController
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
            $user = User::where('email', $request->email)->first()->load('Evaluee', 'Evaluee.question', 'Empleado', 'Empleado.escolaridad', 'Empleado.estado_civil', 'Empleado.tipo_de_sangre', 'Empleado.puesto', 'Empleado.sucursal', 'Empleado.linea', 'Empleado.departamento', 'Empleado.jefe_directo', 'Empleado.archivable', 'Empleado.archivable.requisito', 'Roles');
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
    // -----------------------------------------------------
    public function index()
    {
        return response()->json(User::paginate(5));
    }

    public function all()
    {
        return response()->json(User::with('roles', 'roles.permissions', 'empleado', 'permissions', 'evaluee')->get());
    }

    public function store(StoreRequest $request)
    {
        $user = User::create($request->only(['name', 'email', 'password']));
        $roles = $request->roles;
        $permissions = $request->permissions;

        if (!empty($roles)) {
            $user->syncRoles($roles);
        }
        if (!empty($permissions)) {
            $user->syncPermissions($permissions);
        }
        return response()->json($user->load('roles', 'permissions'));
    }

    public function show(User $user)
    {
        return response()->json($user->load('roles', 'empleado', 'permissions', 'evaluee'));
    }

    public function update(PutRequest $request, User $user)
    {
        $roles = $request->roles;
        $permissions = $request->permissions;
        if ($request->password) {
            $user->update($request->validated());
        } else {
            $user->update($request->only(['name', 'email']));
        }
        $user->syncRoles($roles);
        $user->syncPermissions($permissions);
        return response()->json($user->load('roles', 'permissions'));
        // password_verify($request->password, $user->password // con esto comparas las contraseñas bruta contra la hash
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json("ok");
    }

    public function getRolesPermissions()
    {
        $data = [
            'roles' => Role::all(),
            'permissions' => Permission::all(),
        ];
        return $this->respond($data);
    }
}
