<?php

namespace App\Domains\Tickets\Actions;

use App\Domains\Tickets\TicketType;

class CreateTicketTypeAction
{
    public function execute(array $data, array $companyIds = []): TicketType
    {
        $ticketType = TicketType::create($data);

        if (!empty($companyIds)) {
            $ticketType->companies()->sync($companyIds);
        }

        return $ticketType;
    }
}
