<?php

namespace App\Domains\Tickets\Actions;

use App\Domains\Shared\Actions\AddCommentAction;
use App\Domains\Tickets\Ticket;
use App\Models\User;

class TakeTicketAction
{
    public function __construct(
        private readonly AddCommentAction $addComment
    ) {}

    public function execute(Ticket $ticket, User $user): Ticket
    {
        $ticket->update(['assigned_to' => $user->id]);

        $this->addComment->execute(
            $ticket,
            $user,
            content: 'El ticket fue tomado por ' . $user->name,
            isSystem: true
        );

        return $ticket->fresh();
    }
}
