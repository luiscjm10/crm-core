<?php

namespace App\Domains\Projects;

use App\Domains\Clients\Company;
use App\Domains\Projects\Enums\TaskPriority;
use App\Domains\Projects\Enums\TaskStatus;
use App\Domains\Projects\Enums\TaskType;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name',
    'description',
    'status',
    'due_date',
    'taskable_type',
    'taskable_id',
    'assigned_user_id',
    'creator_id',
    'company_id',
    'is_recurring',
    'recurrence_interval',
    'type',
    'priority',
])]
class Task extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'due_date' => 'date:Y-m-d',
            'is_recurring' => 'boolean',
            'type' => TaskType::class,
            'priority' => TaskPriority::class,
        ];
    }

    public function taskable(): MorphTo
    {
        return $this->morphTo();
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeStatus(Builder $query, TaskStatus $status): Builder
    {
        return $query->where('status', $status->value);
    }

    public function scopePlanned(Builder $query): Builder
    {
        return $this->scopeStatus($query, TaskStatus::Planned);
    }

    public function scopeTodo(Builder $query): Builder
    {
        return $this->scopeStatus($query, TaskStatus::Todo);
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $this->scopeStatus($query, TaskStatus::InProgress);
    }

    public function scopeDone(Builder $query): Builder
    {
        return $this->scopeStatus($query, TaskStatus::Done);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $this->scopeStatus($query, TaskStatus::Cancelled);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString())
            ->whereNotIn('status', [TaskStatus::Done->value, TaskStatus::Cancelled->value]);
    }

    public function isOverdue(): bool
    {
        return $this->due_date !== null
            && $this->due_date->isPast()
            && !in_array($this->status, [TaskStatus::Done->value, TaskStatus::Cancelled->value], true);
    }

    public function isRecurring(): bool
    {
        return $this->is_recurring;
    }
}
