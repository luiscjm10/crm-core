<?php

namespace App\Domains\Tickets\Actions;

use App\Domains\Tickets\Ticket;

class UpdateTicketStatusAction
{
    public function execute(Ticket $ticket, string $status): Ticket
    {
        $ticket->update(['status' => $status]);

        return $ticket->fresh();
    }
}
