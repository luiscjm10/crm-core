<?php

namespace App\Domains\Clients;

use App\Domains\Projects\Task;
use App\Domains\Tickets\Ticket;
use App\Domains\Tickets\TicketType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{

    use HasFactory;

    protected $fillable = [
        'name',
        'tax_id',
        'phone',
        'address',
        'legal_representative',
        'legal_representative_phone',
        'is_active',
        'website',
        'description',
    ];

    /**
     * Obtener todos los usuarios asociados a esta empresa.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_user')->withTimestamps();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function ticketTypes(): BelongsToMany
    {
        return $this->belongsToMany(TicketType::class, 'company_ticket_type');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
