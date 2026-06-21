<?php

namespace App\Http\Controllers\Api;

use App\Domains\Clients\Company;
use App\Http\Controllers\Controller;

class CompanyTicketTypeController extends Controller
{
    public function __invoke(Company $company)
    {
        return response()->json(
            $company->ticketTypes()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'description'])
        );
    }
}
