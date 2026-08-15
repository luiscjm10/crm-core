<?php

namespace App\Domains\Tickets;

use App\Domains\Clients\Company;
use App\Models\User;
use App\Traits\HasComments;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use SoftDeletes, HasComments;

    protected $fillable = [
        'uuid',
        'company_id',
        'ticket_type_id',
        'creator_id',
        'requester_id',
        'assigned_to',
        'status',
        'description',
        'requested_at',
        'closed_at',
    ];

    protected $appends = [
        'total_time_spent_minutes',
        'resolution_time_human',
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function getTotalTimeSpentMinutesAttribute(): int
    {
        if (array_key_exists('comments_sum_time_spent_minutes', $this->attributes)) {
            return (int) $this->attributes['comments_sum_time_spent_minutes'];
        }

        return (int) $this->comments->sum('time_spent_minutes');
    }

    public function getResolutionTimeHumanAttribute(): ?string
    {
        if (! $this->closed_at) {
            return null;
        }

        return $this->created_at->diffForHumans($this->closed_at, true);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $allowed = $user->ticketTypes()->pluck('ticket_type_id');

        if ($allowed->isNotEmpty()) {
            $query->where(function (Builder $typeQuery) use ($allowed, $user) {
                $typeQuery->whereIn('ticket_type_id', $allowed)
                    ->orWhere('assigned_to', $user->id);
            });
        }

        if ($user->hasRole('super-admin')) {
            return $query;
        }

        if ($user->can('tickets.view-all')) {
            $companyIds = $user->companies()->pluck('companies.id')->toArray();
            if ($user->company_id) {
                $companyIds[] = $user->company_id;
            }
            $companyIds = array_unique($companyIds);

            $query->where(function (Builder $q) use ($user, $companyIds) {
                if (!empty($companyIds)) {
                    $q->whereIn('company_id', $companyIds);
                }
                $q->orWhere('creator_id', $user->id)
                    ->orWhere('requester_id', $user->id);
            });

            return $query;
        }

        $query->where(function (Builder $q) use ($user) {
            $q->where('creator_id', $user->id)
                ->orWhere('requester_id', $user->id)
                ->orWhere('assigned_to', $user->id);
        });

        return $query;
    }
}
