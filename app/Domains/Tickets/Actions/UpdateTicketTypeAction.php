<?php

namespace App\Domains\Tickets\Actions;

use App\Domains\Shared\Actions\AddCommentAction;
use App\Domains\Tickets\Ticket;
use App\Domains\Tickets\TicketType;
use App\Models\User;

class UpdateTicketTypeAction
{
    public function __construct(
        private readonly AddCommentAction $addComment
    ) {}

    public function execute(Ticket $ticket, int $ticketTypeId, User $user): Ticket
    {
        $oldType = $ticket->ticketType;
        $newType = TicketType::findOrFail($ticketTypeId);

        $ticket->update(['ticket_type_id' => $ticketTypeId]);

        $this->addComment->execute(
            $ticket,
            $user,
            content: 'El tipo de solicitud cambió de «' . $oldType->name . '» a «' . $newType->name . '» por ' . $user->name,
            isSystem: true
        );

        return $ticket->fresh();
    }
}
