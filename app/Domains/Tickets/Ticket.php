<?php

namespace App\Domains\Tickets;

use App\Domains\Clients\Company;
use App\Models\User;
use App\Traits\HasComments;
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
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'date:Y-m-d',
        ];
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
}
