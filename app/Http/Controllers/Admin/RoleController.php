<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\Auth\Role;
use App\Domains\Auth\Permission;
use App\Domains\Auth\Actions\CreateRoleAction;
use App\Domains\Auth\Actions\UpdateRoleAction;
use App\Domains\Auth\Actions\DeleteRoleAction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array($request->input('perPage'), [10, 20, 50, 100]) ? (int) $request->input('perPage') : 10;

        $roles = Role::with('permissions')->latest()->paginate($perPage)->appends(['perPage' => $perPage]);
        $permissions = Permission::all();

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Roles/Create', [
            'permissions' => Permission::all(),
        ]);
    }

    public function store(Request $request, CreateRoleAction $createRole)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $createRole->execute($validated['name'], $validated['permissions'] ?? []);

        return redirect()->route('admin.roles.index');
    }

    public function edit(Role $role)
    {
        $role->load('permissions');

        return Inertia::render('Admin/Roles/Edit', [
            'role' => $role,
            'permissions' => Permission::all(),
        ]);
    }

    public function update(Request $request, Role $role, UpdateRoleAction $updateRole)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $updateRole->execute($role, $validated['name'], $validated['permissions'] ?? []);

        return redirect()->route('admin.roles.index');
    }

    public function destroy(Role $role, DeleteRoleAction $deleteRole)
    {
        $deleteRole->execute($role);

        return redirect()->route('admin.roles.index');
    }
}
