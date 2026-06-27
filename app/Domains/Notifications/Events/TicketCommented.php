<?php

namespace App\Domains\Notifications\Events;

use App\Domains\Tickets\Ticket;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketCommented
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public User $actor,
        public Comment $comment,
    ) {}
}
