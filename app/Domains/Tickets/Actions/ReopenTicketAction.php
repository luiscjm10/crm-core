<?php

namespace App\Domains\Tickets\Actions;

use App\Domains\Shared\Actions\AddCommentAction;
use App\Domains\Tickets\Ticket;
use App\Models\User;

class ReopenTicketAction
{
    public function __construct(
        private readonly AddCommentAction $addComment
    ) {}

    public function execute(Ticket $ticket, User $user): Ticket
    {
        $ticket->update(['status' => 'open', 'closed_at' => null]);

        $this->addComment->execute(
            $ticket,
            $user,
            content: 'El ticket fue reabierto por ' . $user->name,
            isSystem: true
        );

        return $ticket->fresh();
    }
}
