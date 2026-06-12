<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Task;

class DeleteTaskAction
{
    public function execute(Task $task): void
    {
        $task->delete();
    }
}
