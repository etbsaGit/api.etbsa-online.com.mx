<?php

namespace App\Http\Controllers\Api;

use Spatie\Permission\Models\Role;
use App\Http\Requests\Role\PutRequest;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Role\StoreRequest;
use App\Models\UserTipo;
use Illuminate\Http\Request;

class RoleController extends ApiController
{
    public function indexEmpleado(Request $request)
    {
        $filters = $request->all();
        $tipoUserId = UserTipo::where('name','Empleado')->value('id');

        $roles = Role::filter($filters)
            ->with('permissions', 'users','tipoUser')->where('user_tipo_id',$tipoUserId)
            ->paginate(10);
        return $this->respond($roles);
    }

    public function indexCliente(Request $request)
    {
        $filters = $request->all();
        $tipoUserId = UserTipo::where('name','Cliente')->value('id');

        $roles = Role::filter($filters)
            ->with('permissions', 'users','tipoUser')->where('user_tipo_id',$tipoUserId)
            ->paginate(10);
        return $this->respond($roles);
    }

    public function store(StoreRequest $request)
    {
        $role = Role::create($request->validated());
        return $this->respond($role, 201);
    }

    public function show(Role $role)
    {
        return $this->respond($role->load('permissions', 'users'));
    }

    public function update(PutRequest $request, Role $role)
    {
        $role->update($request->validated());
        return $this->respond($role);
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return $this->respond('ok');
    }

    public function getTiposUser(string $tipo){
        $data = [
            'tipoUser' => UserTipo::where('name',$tipo)->first(),
        ];

        return $this->respond($data);
    }
}
