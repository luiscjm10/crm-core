<?php

namespace App\Domains\Notifications\Listeners;

use App\Domains\Notifications\Concerns\DeterminesRecipients;
use App\Domains\Notifications\Events\TicketClosed;
use App\Domains\Notifications\Events\TicketCommented;
use App\Domains\Notifications\Events\TicketCreated;
use App\Services\PushNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendPushNotification implements ShouldQueue
{
    use DeterminesRecipients;

    public function __construct(
        private readonly PushNotificationService $pushService,
    ) {}

    public function handle(TicketCreated|TicketCommented|TicketClosed $event): void
    {
        $recipients = $this->getRecipients($event);
        $notification = $this->buildNotification($event);

        foreach ($recipients as $user) {
            try {
                $this->pushService->send(
                    $user,
                    $notification['title'],
                    $notification['body'],
                    $notification['data'],
                );
            } catch (\Exception $e) {
                Log::warning("Push notification failed for user {$user->id}: {$e->getMessage()}");
            }
        }
    }

    private function buildNotification(TicketCreated|TicketCommented|TicketClosed $event): array
    {
        $ticket = $event->ticket;
        $companyName = $ticket->company?->name ?? 'Sin empresa';
        $typeName = $ticket->ticketType?->name ?? 'General';
        $description = mb_substr($ticket->description ?? '', 0, 80);
        $url = url(route('admin.tickets.show', $ticket));

        $tag = "ticket-{$ticket->uuid}";

        if ($event instanceof TicketCreated) {
            return [
                'title' => "{$companyName} — Nuevo ticket",
                'body' => "{$typeName}: {$description}",
                'data' => [
                    'url' => $url,
                    'ticketUuid' => $ticket->uuid,
                    'tag' => $tag,
                ],
            ];
        }

        if ($event instanceof TicketCommented) {
            return [
                'title' => "{$companyName} — Nuevo comentario",
                'body' => "{$event->actor->name}: " . mb_substr($event->comment->content ?? '', 0, 80),
                'data' => [
                    'url' => $url,
                    'ticketUuid' => $ticket->uuid,
                    'tag' => "{$tag}-comment",
                ],
            ];
        }

        if ($event instanceof TicketClosed) {
            return [
                'title' => "{$companyName} — Ticket cerrado",
                'body' => "{$typeName} cerrado por {$event->actor->name}",
                'data' => [
                    'url' => $url,
                    'ticketUuid' => $ticket->uuid,
                    'tag' => "{$tag}-closed",
                ],
            ];
        }

        return [
            'title' => 'Notificación',
            'body' => '',
            'data' => [],
        ];
    }
}
