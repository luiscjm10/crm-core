<?php

namespace App\Domains\Auth\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UpdateUserAction
{
    public function execute(User $user, array $data, ?string $roleName = null): User
    {
        $fillable = [
            'name' => $data['name'],
            'email' => $data['email'],
            'company_id' => $data['company_id'] ?? $user->company_id,
        ];

        if (!empty($data['password'])) {
            $fillable['password'] = Hash::make($data['password']);
        }

        $user->update($fillable);

        if ($roleName) {
            $role = Role::findByName($roleName, 'web');
            $user->syncRoles([$role]);
        } else {
            $user->syncRoles([]);
        }

        return $user->fresh();
    }
}
