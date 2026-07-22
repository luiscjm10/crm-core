<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\TaskType;

class CreateTaskTypeAction
{
    public function execute(array $data): TaskType
    {
        return TaskType::create($data);
    }
}
