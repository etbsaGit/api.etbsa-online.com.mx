<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use Spatie\Permission\Models\Permission;
use App\Http\Requests\Permission\PutRequest;
use App\Http\Requests\Permission\StoreRequest;
use Illuminate\Http\Request;
class PermissionController extends ApiController
{
    public function index(Request $request)
    {
        $filters = $request->all();

        $permissions = Permission::filter($filters)
            ->with('roles')
            ->paginate(10);
        return $this->respond($permissions);

    }

    public function store(StoreRequest $request)
    {
        $permission = Permission::create($request->validated());
        return $this->respond($permission, 201);
    }

    public function show(Permission $permission)
    {
        return $this->respond($permission->load('roles'));
    }

    public function update(PutRequest $request, Permission $permission)
    {
       $permission->update($request->validated());
       return $this->respond($permission);
    }

    public function destroy(Permission $permission) {
        $permission->delete();
        return $this->respond('ok');
    }
}
