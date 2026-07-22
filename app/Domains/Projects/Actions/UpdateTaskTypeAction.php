<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\TaskType;

class UpdateTaskTypeAction
{
    public function execute(TaskType $taskType, array $data): TaskType
    {
        $taskType->update($data);

        return $taskType;
    }
}
