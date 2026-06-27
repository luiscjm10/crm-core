<?php

namespace App\Domains\Notifications\Listeners;

use App\Domains\Notifications\Concerns\DeterminesRecipients;
use App\Domains\Notifications\Events\TicketClosed;
use App\Domains\Notifications\Events\TicketCommented;
use App\Domains\Notifications\Events\TicketCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;

class StoreDatabaseNotification implements ShouldQueue
{
    use DeterminesRecipients;

    public function handle(TicketCreated|TicketCommented|TicketClosed $event): void
    {
        $recipients = $this->getRecipients($event);
        $data = $this->buildData($event);

        $type = match (true) {
            $event instanceof TicketCreated => 'ticket.created',
            $event instanceof TicketCommented => 'ticket.commented',
            $event instanceof TicketClosed => 'ticket.closed',
        };

        foreach ($recipients as $user) {
            $user->notifications()->create([
                'id' => (string) Str::uuid(),
                'type' => $type,
                'data' => $data,
            ]);
        }
    }

    private function buildData(TicketCreated|TicketCommented|TicketClosed $event): array
    {
        $ticket = $event->ticket;
        $url = route('admin.tickets.show', $ticket);

        $base = [
            'ticket_uuid' => $ticket->uuid,
            'company_name' => $ticket->company?->name ?? 'Sin empresa',
            'ticket_type' => $ticket->ticketType?->name ?? 'General',
            'actor_name' => $event->actor->name,
            'url' => $url,
        ];

        if ($event instanceof TicketCreated) {
            $base['description'] = 'Nuevo ticket creado';
            $base['description_detail'] = mb_substr($ticket->description ?? '', 0, 100);
        } elseif ($event instanceof TicketCommented) {
            $base['description'] = 'Nuevo comentario';
            $base['description_detail'] = mb_substr($event->comment->content ?? '', 0, 100);
        } elseif ($event instanceof TicketClosed) {
            $base['description'] = 'Ticket cerrado';
            $base['description_detail'] = 'El ticket fue cerrado por ' . $event->actor->name;
        }

        return $base;
    }
}
