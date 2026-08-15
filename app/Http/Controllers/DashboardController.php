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
        $query = Ticket::query()->visibleTo($user);

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
