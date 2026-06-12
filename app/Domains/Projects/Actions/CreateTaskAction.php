<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Task;

class CreateTaskAction
{
    public function execute(array $data): Task
    {
        if (auth()->check()) {
            $data['creator_id'] = auth()->id();
        }

        return Task::create($data);
    }
}
