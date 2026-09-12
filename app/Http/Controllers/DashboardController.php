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

        $dateField = in_array($request->get('date_field'), ['requested_at', 'executed_at'])
            ? $request->get('date_field') : 'requested_at';

        $query = Ticket::query()->visibleTo($user);
        $query->whereBetween($dateField, [now()->startOfMonth(), now()->endOfMonth()]);

        $stats = [
            'total' => (clone $query)->count(),
            'open' => (clone $query)->where('status', 'open')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'closed' => (clone $query)->where('status', 'closed')->count(),
        ];

        $globalQuery = Ticket::query()->visibleTo($user);
        $globalStats = [
            'total' => (clone $globalQuery)->count(),
            'open' => (clone $globalQuery)->where('status', 'open')->count(),
            'in_progress' => (clone $globalQuery)->where('status', 'in_progress')->count(),
            'closed' => (clone $globalQuery)->where('status', 'closed')->count(),
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'globalStats' => $globalStats,
            'period' => now()->translatedFormat('F Y'),
            'periodStart' => now()->startOfMonth()->format('Y-m-d'),
            'periodEnd' => now()->endOfMonth()->format('Y-m-d'),
            'dateField' => $dateField,
        ]);
    }
}
