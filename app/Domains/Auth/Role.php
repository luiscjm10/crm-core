<?php

namespace App\Domains\Auth;

use Spatie\Permission\Models\Role as BaseRole;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends BaseRole
{
    protected $guard_name = 'web';

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions', 'role_id', 'permission_id');
    }
}
