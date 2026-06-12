<?php

namespace App\Domains\Auth\Actions;

use App\Domains\Auth\Role;

class DeleteRoleAction
{
    public function execute(Role $role): void
    {
        $role->syncPermissions([]);
        $role->delete();
    }
}
