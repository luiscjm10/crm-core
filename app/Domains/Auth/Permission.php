<?php

namespace App\Domains\Auth;

use Spatie\Permission\Models\Permission as BasePermission;

class Permission extends BasePermission
{
    protected $guard_name = 'web';
}
