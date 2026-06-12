<?php

namespace App\Domains\Auth\Actions;

use App\Domains\Projects\Task;
use App\Models\User;

class DeleteUserAction
{
    public function execute(User $user): void
    {
        Task::where('assigned_user_id', $user->id)->update(['assigned_user_id' => null]);
        Task::where('creator_id', $user->id)->update(['creator_id' => null]);

        $user->syncRoles([]);
        $user->delete();
    }
}
