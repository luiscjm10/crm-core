<?php

namespace App\Domains\Tickets\Actions;

use App\Domains\Tickets\TicketType;

class UpdateTicketTypeAction
{
    public function execute(TicketType $ticketType, array $data, array $companyIds = []): TicketType
    {
        $ticketType->update($data);

        if (!empty($companyIds)) {
            $ticketType->companies()->sync($companyIds);
        }

        return $ticketType;
    }
}
