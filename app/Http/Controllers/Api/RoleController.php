<?php

namespace App\Http\Controllers\Api;

use Spatie\Permission\Models\Role;
use App\Http\Requests\Role\PutRequest;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Role\StoreRequest;
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

    public function destroy(Role $role) {
        $role->delete();
        return response()->json('ok');
    }

    public function attachPermission(Role $role, Permission $permission)
    {
        $role->givePermissionTo($permission);

        return response()->json(['message' => 'Permiso asignado al rol correctamente'], 200);
    }

    public function detachPermission(Role $role, Permission $permission)
    {
        $role->revokePermissionTo($permission);

        return response()->json(['message' => 'Permiso removido del rol correctamente'], 200);
    }
}
