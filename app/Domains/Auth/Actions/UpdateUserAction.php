<?php

namespace App\Domains\Auth\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UpdateUserAction
{
    public function execute(User $user, array $data, ?string $roleName = null, array $companyIds = []): User
    {
        $fillable = [
            'name' => $data['name'],
            'last_name' => $data['last_name'] ?? $user->last_name,
            'email' => $data['email'],
            'company_id' => array_key_exists('company_id', $data) ? $data['company_id'] : $user->company_id,
        ];

        if (!empty($data['password'])) {
            $fillable['password'] = Hash::make($data['password']);
        }

        $user->update($fillable);

        $user->companies()->sync($companyIds);

        if ($roleName) {
            $role = Role::findByName($roleName, 'web');
            $user->syncRoles([$role]);
        } else {
            $user->syncRoles([]);
        }

        return $user->fresh();
    }
}
