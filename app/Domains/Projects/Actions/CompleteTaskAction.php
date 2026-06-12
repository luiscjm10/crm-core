<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Enums\TaskStatus;
use App\Domains\Projects\Task;
use Illuminate\Support\Facades\DB;

class CompleteTaskAction
{
    public function execute(Task $task): Task
    {
        return DB::transaction(function () use ($task) {
            $task->update(['status' => TaskStatus::Done->value]);

            if ($task->is_recurring && $task->recurrence_interval && $task->due_date) {
                $nextDueDate = $this->calculateNextDueDate($task->due_date, $task->recurrence_interval);

                $recurringTask = Task::create([
                    'name' => $task->name,
                    'description' => $task->description,
                    'status' => TaskStatus::Planned->value,
                    'due_date' => $nextDueDate,
                    'taskable_type' => $task->taskable_type,
                    'taskable_id' => $task->taskable_id,
                    'company_id' => $task->company_id,
                    'assigned_user_id' => $task->assigned_user_id,
                    'creator_id' => $task->creator_id,
                    'is_recurring' => true,
                    'recurrence_interval' => $task->recurrence_interval,
                    'type' => $task->type,
                    'priority' => $task->priority,
                ]);

                $recurringTask->save();
            }

            return $task->fresh();
        });
    }

    private function calculateNextDueDate(\Carbon\Carbon $dueDate, string $interval): \Carbon\Carbon
    {
        return match ($interval) {
            'daily' => $dueDate->copy()->addDay(),
            'weekly' => $dueDate->copy()->addWeek(),
            'biweekly' => $dueDate->copy()->addWeeks(2),
            'monthly' => $dueDate->copy()->addMonth(),
            'quarterly' => $dueDate->copy()->addMonths(3),
            'semi-annually' => $dueDate->copy()->addMonths(6),
            'annually' => $dueDate->copy()->addYear(),
            default => $dueDate->copy()->addMonth(),
        };
    }
}
