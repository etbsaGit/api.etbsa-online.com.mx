<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use App\Http\Requests\User\AttachRequest;
use App\Http\Requests\User\StoreRequest;
use App\Http\Requests\User\PutRequest;

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
            $user = User::where('email', $request->email)->first()->load('Empleado','Empleado.escolaridad','Empleado.estado_civil','Empleado.tipo_de_sangre','Empleado.puesto','Empleado.sucursal','Empleado.linea','Empleado.departamento','Empleado.jefe_directo','Empleado.archivable','Empleado.archivable.requisito','Roles');
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
        return response()->json(User::with('roles','roles.permissions','empleado')->get());
    }

    public function store(StoreRequest $request)
    {
        return response()->json(User::create($request->validated()));
    }

    public function show(User $user)
    {
        return response()->json($user->load('roles','roles.permissions','empleado'));
    }

    public function update(PutRequest $request, User $user)
    {

        if ($request->password) {
            $user->update($request->validated());
        } else {
            $user->update($request->only(['name','email']));
        }

        return response()->json($user);

        // password_verify($request->password, $user->password // con esto comparas las contraseñas bruta contra la hash

    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json("ok");
    }

    //-------------------------------------------------------------

    public function assignRoleToUser(User $user, AttachRequest $request)
    {
        $roles = $request->roles;

        if (!empty($roles)) {
            $user->syncRoles($roles);
            return response()->json(['message' => 'Rol asignado al usuario correctamente'], 200);
        } else {
            return response()->json(['message' => 'No hay roles que asignar'], 400);
        }
    }

    public function revokeRoleToUser(User $user, AttachRequest $request)
    {
        $roles = $request->roles;

        if (!empty($roles)) {
            foreach ($roles as $role){
                $user->removeRole($role);
            }
            return response()->json(['message' => 'Roles quitados al usuario correctamente'], 200);
        } else {
            return response()->json(['message' => 'No hay roles que quitar'], 400);
        }
    }

    public function getPermissionsForAUser(User $user)  {
        $permissions = $user->getAllPermissions();
        return response()->json($permissions);
    }

    public function getRolesForAUser(User $user)  {
        $RoleNames = $user->getRoleNames();
        return response()->json($RoleNames);
    }
}
