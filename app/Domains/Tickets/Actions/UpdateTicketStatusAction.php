<?php

namespace App\Domains\Tickets\Actions;

use App\Domains\Shared\Actions\AddCommentAction;
use App\Domains\Tickets\Ticket;
use App\Models\User;

class UpdateTicketStatusAction
{
    private const STATUS_LABELS = [
        'open' => 'Abierto',
        'in_progress' => 'En progreso',
        'closed' => 'Cerrado',
    ];

    public function __construct(
        private readonly AddCommentAction $addComment
    ) {}

    public function execute(Ticket $ticket, string $status, User $user): Ticket
    {
        $data = ['status' => $status];

        if ($status === 'in_progress') {
            $data['executed_at'] = now();
        }

        $ticket->update($data);

        $label = self::STATUS_LABELS[$status] ?? $status;

        $this->addComment->execute(
            $ticket,
            $user,
            content: 'El ticket pasó a estado ' . $label . ' por ' . $user->name,
            isSystem: true
        );

        return $ticket->fresh();
    }
}
