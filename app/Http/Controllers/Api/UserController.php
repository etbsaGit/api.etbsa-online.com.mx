<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Mail\TwoFactorCodeMail;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\User\PutRequest;
use App\Http\Controllers\ApiController;
use App\Http\Requests\User\StoreRequest;
use Illuminate\Support\Facades\Password;
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
            $user = User::where('email', $request->email)->first();

            // Generar y guardar el código 2FA
            $user->two_factor_code = rand(100000, 999999);
            $user->two_factor_expires_at = now()->addMinutes(10);
            $user->save();

            // Enviar código por correo
            Mail::to($user->email)->send(new TwoFactorCodeMail($user));

            // Cerrar sesión por seguridad hasta que se verifique el código
            Auth::logout();

            return response()->json([
                'status' => true,
                'message' => 'Código de verificación enviado al correo',
                'two_factor_required' => true,
                'user_id' => $user->id
            ]);
        }

        return response()->json("Usuario y/o contraseña inválido", 401);
    }

    public function verify2FA(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'two_factor_code' => 'required|numeric',
        ]);

        $user = User::find($request->user_id);

        if (
            !$user->two_factor_code ||
            $user->two_factor_code !== $request->two_factor_code ||
            now()->gt($user->two_factor_expires_at)
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Código incorrecto o expirado'
            ], 401);
        }

        // Limpiar el código 2FA
        $user->two_factor_code = null;
        $user->two_factor_expires_at = null;
        $user->save();

        Auth::login($user);

        // Cargar relaciones (igual que en login original)
        $user->load(
            'Evaluee',
            'Evaluee.question',
            'Empleado',
            'Empleado.escolaridad',
            'Empleado.estado_civil',
            'Empleado.tipo_de_sangre',
            'Empleado.puesto',
            'Empleado.sucursal',
            'Empleado.linea',
            'Empleado.departamento',
            'Empleado.jefe_directo',
            'Empleado.archivable',
            'Empleado.vehicle',
            'Empleado.archivable.requisito',
            'Empleado.empleadosContact.kinship',
            'Roles',
            'Permissions'
        );

        return response()->json([
            'status' => true,
            'message' => 'Verificación 2FA exitosa',
            'data' => $user,
            'token' => $user->createToken('myapptoken')->plainTextToken
        ]);
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Correo enviado con el enlace para restablecer tu contraseña.'])
            : response()->json(['message' => 'No se pudo enviar el correo.'], 400);
    }

     public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => bcrypt($password)
                ])->save();

                // Opcional: eventos o login automático después del reset
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Contraseña restablecida con éxito.']);
        } else {
            return response()->json(['message' => __($status)], 400);
        }
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
