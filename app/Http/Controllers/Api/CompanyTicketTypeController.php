<?php

namespace App\Http\Controllers\Api;

use App\Domains\Clients\Company;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CompanyTicketTypeController extends Controller
{
    public function __invoke(Request $request, Company $company)
    {
        $query = $company->ticketTypes()
            ->where('is_active', true)
            ->orderBy('name');

        $allowed = $request->user()->ticketTypes()->pluck('ticket_type_id');

        if ($allowed->isNotEmpty()) {
            $query->whereIn('ticket_type_id', $allowed);
        }

        return response()->json(
            $query->get(['id', 'name', 'description'])
        );
    }
}
