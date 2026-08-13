<?php

namespace App\Http\Controllers;

use App\Domains\Tickets\Ticket;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Ticket::query();

        if ($user->hasRole('super-admin')) {
        } elseif ($user->can('tickets.view-all')) {
            $companyIds = $user->companies()->pluck('companies.id')->toArray();
            if ($user->company_id) {
                $companyIds[] = $user->company_id;
            }
            $companyIds = array_unique($companyIds);
            $query->where(function ($q) use ($user, $companyIds) {
                if (!empty($companyIds)) {
                    $q->whereIn('company_id', $companyIds);
                }
                $q->orWhere('creator_id', $user->id)
                  ->orWhere('requester_id', $user->id);
            });
        } else {
            $query->where(function ($q) use ($user) {
                $q->where('creator_id', $user->id)
                  ->orWhere('requester_id', $user->id);
            });
        }

        $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);

        $stats = [
            'open' => (clone $query)->where('status', 'open')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'closed' => (clone $query)->where('status', 'closed')->count(),
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'period' => now()->translatedFormat('F Y'),
        ]);
    }
}
