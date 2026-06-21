<?php

namespace App\Domains\Shared\Actions;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AddCommentAction
{
    public function execute(Model $model, User $user, string $content, bool $isSystem = false, ?int $timeSpentMinutes = null): Comment
    {
        return $model->comments()->create([
            'user_id' => $user->id,
            'content' => $content,
            'is_system' => $isSystem,
            'time_spent_minutes' => $timeSpentMinutes,
        ]);
    }
}
