<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleAndAdminSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissionsData = [
            ['name' => 'users.create',     'description' => 'Crear nuevos usuarios',                'group' => 1],
            ['name' => 'users.read',       'description' => 'Ver listado y detalle de usuarios',     'group' => 1],
            ['name' => 'users.update',     'description' => 'Editar usuarios existentes',            'group' => 1],
            ['name' => 'users.delete',     'description' => 'Eliminar usuarios del sistema',         'group' => 1],
            ['name' => 'roles.create',     'description' => 'Crear nuevos roles',                    'group' => 2],
            ['name' => 'roles.read',       'description' => 'Ver listado y detalle de roles',        'group' => 2],
            ['name' => 'roles.update',     'description' => 'Editar roles y sus permisos',           'group' => 2],
            ['name' => 'roles.delete',     'description' => 'Eliminar roles del sistema',            'group' => 2],
            ['name' => 'companies.create', 'description' => 'Crear nuevas compañías',                'group' => 3],
            ['name' => 'companies.read',   'description' => 'Ver listado y detalle de compañías',    'group' => 3],
            ['name' => 'companies.update', 'description' => 'Editar datos de compañías existentes',  'group' => 3],
            ['name' => 'companies.delete', 'description' => 'Eliminar compañías del sistema',        'group' => 3],
            ['name' => 'tasks.create',     'description' => 'Crear nuevas tareas',                   'group' => 4],
            ['name' => 'tasks.read',       'description' => 'Ver listado y detalle de tareas',       'group' => 4],
            ['name' => 'tasks.update',     'description' => 'Editar tareas existentes',              'group' => 4],
            ['name' => 'tasks.delete',     'description' => 'Eliminar tareas del sistema',           'group' => 4],
            ['name' => 'tasks.complete',   'description' => 'Marcar tareas como completadas',        'group' => 4],
            ['name' => 'tickets.create',   'description' => 'Crear nuevas solicitudes',              'group' => 5],
            ['name' => 'tickets.read',     'description' => 'Ver listado y detalle de solicitudes',   'group' => 5],
            ['name' => 'tickets.update',   'description' => 'Editar solicitudes existentes',          'group' => 5],
            ['name' => 'tickets.delete',   'description' => 'Eliminar solicitudes del sistema',       'group' => 5],
            ['name' => 'tickets.view-all', 'description' => 'Ver solicitudes de compañías asignadas',   'group' => 5],
            ['name' => 'ticket-types.create', 'description' => 'Crear tipos de solicitud',             'group' => 5],
            ['name' => 'ticket-types.read',   'description' => 'Ver listado de tipos de solicitud',    'group' => 5],
            ['name' => 'ticket-types.update', 'description' => 'Editar tipos de solicitud',            'group' => 5],
            ['name' => 'ticket-types.delete', 'description' => 'Eliminar tipos de solicitud',          'group' => 5],
            ['name' => 'tickets.assign-requester', 'description' => 'Seleccionar solicitante al crear ticket', 'group' => 5],
            ['name' => 'tickets.close', 'description' => 'Cerrar tickets del sistema', 'group' => 5],
            ['name' => 'tickets.comment', 'description' => 'Comentar en tickets del sistema', 'group' => 5],
        ];

        $permissionNames = [];

        foreach ($permissionsData as $data) {
            Permission::firstOrCreate(
                ['name' => $data['name'], 'guard_name' => 'web'],
                ['description' => $data['description'], 'group' => $data['group']]
            );
            $permissionNames[] = $data['name'];
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $roleAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $roleAdmin->syncPermissions($permissionNames);

        $roleAgente = Role::firstOrCreate(['name' => 'agente', 'guard_name' => 'web']);
        $agentePermissions = array_filter($permissionNames, fn($p) => str_ends_with($p, '.read'));
        $roleAgente->syncPermissions($agentePermissions);

        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Administrator',
                'last_name' => 'Admin',
                'password' => Hash::make('password123'),
            ]
        );

        if (!$admin->hasRole($roleAdmin)) {
            $admin->assignRole($roleAdmin);
        }
    }
}
