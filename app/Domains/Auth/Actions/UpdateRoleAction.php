<?php

namespace App\Domains\Auth\Actions;

use App\Domains\Auth\Role;

class UpdateRoleAction
{
    public function execute(Role $role, string $name, array $permissionIds = []): Role
    {
        $role->update(['name' => $name]);

        if (!empty($permissionIds)) {
            $role->syncPermissions($permissionIds);
        } else {
            $role->syncPermissions([]);
        }

        return $role->fresh();
    }
}
