<?php

namespace App\Http\Controllers\Api;

use Spatie\Permission\Models\Role;
use App\Http\Requests\Role\PutRequest;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Role\StoreRequest;

class RoleController extends ApiController
{
    public function index()
    {
        return response()->json(Role::with(['permissions','users'])->get());
    }

    public function store(StoreRequest $request)
    {
        $role = Role::create($request->only(['name']));

        $permissions = $request->permissions;

        if (!empty($permissions)) {
            $role->syncPermissions($permissions);
        }

        return response()->json($role->load('permissions'));
    }

    public function show(Role $role)
    {
        return response()->json($role->load('permissions','users'));
    }

    public function update(PutRequest $request, Role $role)
    {
        $role->update($request->only(['name']));

        if (!empty($request->permissions)) {
            $permissions = $request->permissions;
            $role->syncPermissions($permissions);
        } else {
            $permissions = $role->getAllPermissions();
            foreach ($permissions as $permission) {
                $role->revokePermissionTo($permission->name);
            }
        }
        return response()->json($role->load('permissions'));
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json('ok');
    }
}
