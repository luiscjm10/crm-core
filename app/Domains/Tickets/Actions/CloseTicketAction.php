<?php

namespace App\Domains\Tickets\Actions;

use App\Domains\Shared\Actions\AddCommentAction;
use App\Domains\Tickets\Ticket;
use App\Models\User;

class CloseTicketAction
{
    public function __construct(
        private readonly AddCommentAction $addComment
    ) {}

    public function execute(Ticket $ticket, User $user): void
    {
        $ticket->update(['status' => 'closed']);

        $this->addComment->execute(
            $ticket,
            $user,
            content: 'El ticket fue cerrado por ' . $user->name,
            isSystem: true
        );
    }
}
