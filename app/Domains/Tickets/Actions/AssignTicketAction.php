<?php

namespace App\Domains\Tickets\Actions;

use App\Domains\Tickets\Ticket;

class AssignTicketAction
{
    public function execute(Ticket $ticket, ?int $userId): Ticket
    {
        $ticket->update(['assigned_to' => $userId]);

        return $ticket->fresh();
    }
}
