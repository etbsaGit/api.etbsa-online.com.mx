<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use App\Mail\VerifyEmail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\TwoFactorCodeMail;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\URL;
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

            // Si el usuario NO tiene email verificado → login directo sin 2FA
            if (is_null($user->email_verified_at)) {
                // Cargar relaciones necesarias
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

                return $this->respond([
                    'status' => true,
                    'message' => 'Login exitoso sin 2FA (email no verificado)',
                    'data' => $user,
                    'token' => $user->createToken('myapptoken')->plainTextToken
                ]);
            }

            // Si el email está verificado → flujo normal de 2FA
            $user->two_factor_code = rand(100000, 999999);
            $user->two_factor_expires_at = now()->addMinutes(10);
            $user->save();

            Mail::to($user->email)->send(new TwoFactorCodeMail($user));

            Auth::logout(); // cerrar sesión hasta que verifique el código

            return $this->respond([
                'status' => true,
                'message' => 'Código de verificación enviado al correo',
                'two_factor_required' => true,
                'user_id' => $user->id
            ]);
        }

        return $this->respondError("Usuario y/o contraseña inválido", 403);
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
            return $this->respondError(
                'Código incorrecto o expirado',
                403
            );
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

        return $this->respond([
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
            ? $this->respond(['message' => 'Correo enviado con el enlace para restablecer tu contraseña.'])
            : $this->respond(['message' => 'No se pudo enviar el correo.'], 400);
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
            return $this->respond(['message' => 'Contraseña restablecida con éxito.']);
        } else {
            return $this->respond(['message' => __($status)], 400);
        }
    }

    public function enviarCorreoVerificacion(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return $this->respond(['message' => 'Usuario no autenticado'], 401);
        }

        $user->email_verification_token = Str::random(60);
        $user->save();

        $verificationUrl = config('app.frontend_url') . "/verificar-correo?token=" . $user->email_verification_token;

        Mail::to($user->email)->send(new VerifyEmail($verificationUrl));

        return $this->respond(['message' => 'Correo de verificación enviado']);
    }

    public function verificarCorreo(Request $request)
    {
        $token = $request['token'];

        if (!$token) {
            return $this->respond(['message' => 'Token no proporcionado'], 400);
        }

        $user = User::where('email_verification_token', $token)->first();

        if (!$user) {
            return $this->respond(['message' => 'Token inválido'], 404);
        }

        $user->email_verified_at = Carbon::now();
        $user->email_verification_token = null;
        $user->save();

        // Aquí podrías redirigir al front o devolver JSON según necesites
        return $this->respond(['message' => 'Correo verificado correctamente']);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return $this->respond('Logout exitoso');
    }
    // -----------------------------------------------------
    public function index()
    {
        return $this->respond(User::get());
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
        return $this->respond($user->load('roles', 'permissions'));
    }

    public function show(User $user)
    {
        return $this->respond($user->load('roles', 'empleado', 'permissions', 'evaluee'));
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
        return $this->respond($user->load('roles', 'permissions'));
    }

    public function destroy(User $user)
    {
        $user->delete();
        return $this->respond("ok");
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
            return $this->respond('Contraseña cambiada con exito');
        }
        return $this->respond('Contraseña actual no valida', 403);
    }
}
