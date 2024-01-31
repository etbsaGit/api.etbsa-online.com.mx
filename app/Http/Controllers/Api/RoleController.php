<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Requests\Role\PutRequest;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Role\StoreRequest;
use App\Http\Requests\Role\AttachRequest;
use Illuminate\Contracts\Mail\Attachable;
use Spatie\Permission\Models\Permission;


class RoleController extends ApiController
{
    public function index()
    {
        return response()->json(Role::with('permissions')->get());
    }

    public function store(StoreRequest $request)
    {
        $rol = Role::create($request->validated());
        return response()->json($rol, 201);
    }

    public function show(Role $role)
    {
        return response()->json($role->load('permissions'));
    }

    public function update(PutRequest $request, Role $role)
    {
        $role->update($request->validated());
        return response()->json($role);
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json('ok');
    }

    public function attachPermissionsToRole(Role $role, AttachRequest $request)
    {
        $permissions = $request->permissions;

        $role->syncPermissions($permissions);

        return response()->json(['message' => 'Permisos asignados al rol correctamente'], 200);
    }

    public function detachPermissionsFromRole(Role $role, AttachRequest $request)
    {

        $permissions = ($request->permissions);

        if (!empty($permissions)) {
            foreach ($permissions as $permission) {
                $role->revokePermissionTo($permission);
            }
        }

        return response()->json(['message' => 'Permisos desasociados del rol correctamente'], 200);
    }
}
