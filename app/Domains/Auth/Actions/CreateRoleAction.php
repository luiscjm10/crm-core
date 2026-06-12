<?php

namespace App\Domains\Auth\Actions;

use App\Domains\Auth\Role;

class CreateRoleAction
{
    public function execute(string $name, array $permissionIds = []): Role
    {
        $role = Role::create(['name' => $name]);

        if (!empty($permissionIds)) {
            $role->syncPermissions($permissionIds);
        }

        return $role;
    }
}
