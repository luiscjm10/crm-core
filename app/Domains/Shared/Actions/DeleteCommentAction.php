<?php

namespace App\Domains\Shared\Actions;

use App\Models\Comment;

class DeleteCommentAction
{
    public function execute(Comment $comment): void
    {
        $comment->delete();
    }
}
