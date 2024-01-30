<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use Spatie\Permission\Models\Permission;
use App\Http\Requests\Permission\PutRequest;
use App\Http\Requests\Permission\StoreRequest;


class PermissionController extends ApiController
{
    public function index()
    {
        return response()->json(Permission::with('roles')->get());
    }

    public function store(StoreRequest $request)
    {
        $permission = Permission::create($request->validated());
        return response()->json($permission, 201);
    }

    public function show(Permission $permission)
    {
        return response()->json($permission->load('roles'));
    }

    public function update(PutRequest $request, Permission $permission)
    {
       $permission->update($request->validated());
       return response()->json($permission);
    }

    public function destroy(Permission $permission) {
        $permission->delete();
        return response()->json('ok');
    }
}
