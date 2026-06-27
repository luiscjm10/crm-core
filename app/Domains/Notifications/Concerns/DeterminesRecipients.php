<?php

namespace App\Domains\Notifications\Concerns;

use App\Domains\Notifications\Events\TicketClosed;
use App\Domains\Notifications\Events\TicketCommented;
use App\Domains\Notifications\Events\TicketCreated;
use App\Models\User;

trait DeterminesRecipients
{
    protected function getRecipients(TicketCreated|TicketCommented|TicketClosed $event): array
    {
        $ticket = $event->ticket;
        $actor = $event->actor;
        $userIds = [];

        if ($ticket->creator_id && $ticket->creator_id !== $actor->id) {
            $userIds[] = $ticket->creator_id;
        }
        if ($ticket->requester_id && $ticket->requester_id !== $actor->id) {
            $userIds[] = $ticket->requester_id;
        }
        if ($ticket->assigned_to && $ticket->assigned_to !== $actor->id) {
            $userIds[] = $ticket->assigned_to;
        }

        $userIds = array_unique(array_filter($userIds));

        if (empty($userIds)) {
            return [];
        }

        return User::whereIn('id', $userIds)->get()->all();
    }
}
