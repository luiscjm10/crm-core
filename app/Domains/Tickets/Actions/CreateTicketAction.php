<?php

namespace App\Domains\Tickets\Actions;

use App\Domains\Tickets\Ticket;
use Illuminate\Support\Str;

class CreateTicketAction
{
    public function execute(array $data, ?int $creatorId = null, ?int $requesterId = null): Ticket
    {
        $data['uuid'] = Str::uuid()->toString();

        if ($creatorId) {
            $data['creator_id'] = $creatorId;
        }

        if ($requesterId) {
            $data['requester_id'] = $requesterId;
        }

        if (empty($data['requested_at'])) {
            $data['requested_at'] = now();
        }

        return Ticket::create($data);
    }
}
