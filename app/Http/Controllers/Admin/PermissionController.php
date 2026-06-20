<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\Auth\Permission;
use Inertia\Inertia;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:roles.read')->only('index');
    }

    public function index()
    {
        $permissions = Permission::all();

        return Inertia::render('Admin/Roles/Index', [
            'permissions' => $permissions,
        ]);
    }
}
