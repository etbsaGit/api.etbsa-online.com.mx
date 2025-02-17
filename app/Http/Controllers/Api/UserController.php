<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\User\PutRequest;
use App\Http\Controllers\ApiController;
use App\Http\Requests\User\StoreRequest;
use Spatie\Permission\Models\Permission;
use App\Http\Requests\User\PasswordRequest;

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
            $user = User::where('email', $request->email)->first()->load('Evaluee', 'Evaluee.question', 'Empleado', 'Empleado.escolaridad', 'Empleado.estado_civil', 'Empleado.tipo_de_sangre', 'Empleado.puesto', 'Empleado.sucursal', 'Empleado.linea', 'Empleado.departamento', 'Empleado.jefe_directo', 'Empleado.archivable', 'Empleado.archivable.requisito', 'Roles', 'Permissions');
            return response()->json([
                'status' => true,
                'message' => 'Usuario logueado con exito',
                'data' => $user,
                'token' => $user->createToken('myapptoken')->plainTextToken
            ]);
        }
        return response()->json("Usuario y/o contraseña inválido", 401);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json('Logout exitoso');
    }
    // -----------------------------------------------------
    public function index()
    {
        return response()->json(User::get());
    }

    public function all(Request $request)
    {
        $filters = $request->all();
        $users = User::filter($filters)
            ->with('roles', 'roles.permissions', 'empleado', 'permissions', 'evaluee')
            ->orderBy('email')
            ->paginate(10);
        return $this->respond($users);
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

    public function changePassword(PasswordRequest $request)
    {
        $user = Auth::user();
        if (password_verify($request->old_password, $user->password)) {
            $user->update($request->only(['password']));
            return response()->json('Contraseña cambiada con exito');
        }
        return response()->json('Contraseña actual no valida', 403);
    }
}
