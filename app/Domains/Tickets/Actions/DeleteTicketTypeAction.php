<?php

namespace App\Domains\Tickets\Actions;

use App\Domains\Tickets\TicketType;

class DeleteTicketTypeAction
{
    public function execute(TicketType $ticketType): void
    {
        $ticketType->delete();
    }
}
