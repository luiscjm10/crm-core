<?php

namespace App\Domains\Auth\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreateUserAction
{
    public function execute(array $data, ?string $roleName = null, array $companyIds = []): User
    {
        $user = User::create([
            'name' => $data['name'],
            'last_name' => $data['last_name'] ?? null,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'company_id' => $data['company_id'] ?? null,
        ]);

        if (!empty($companyIds)) {
            $user->companies()->sync($companyIds);
        }

        if ($roleName) {
            $role = Role::findByName($roleName, 'web');
            $user->assignRole($role);
        }

        return $user;
    }
}
