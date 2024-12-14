<?php

namespace App\Http\Controllers\Api;

use Spatie\Permission\Models\Role;
use App\Http\Requests\Role\PutRequest;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Role\StoreRequest;
use Illuminate\Http\Request;

class RoleController extends ApiController
{
    public function index(Request $request)
    {
        $filters = $request->all();

        $roles = Role::filter($filters)
            ->with('permissions', 'users')
            ->paginate(10);
        return $this->respond($roles);

    }

    public function store(StoreRequest $request)
    {
        $role = Role::create($request->validated());
        return response()->json($role, 201);
    }

    public function show(Role $role)
    {
        return response()->json($role->load('permissions', 'users'));
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
}
