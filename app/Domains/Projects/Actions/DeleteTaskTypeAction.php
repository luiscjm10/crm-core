<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\TaskType;

class DeleteTaskTypeAction
{
    public function execute(TaskType $taskType): void
    {
        $taskType->delete();
    }
}
