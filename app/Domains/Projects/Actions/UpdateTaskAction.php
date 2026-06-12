<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Task;

class UpdateTaskAction
{
    public function execute(Task $task, array $data): Task
    {
        $task->update($data);

        return $task->fresh();
    }
}
