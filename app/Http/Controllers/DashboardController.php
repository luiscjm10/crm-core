<?php

namespace App\Http\Controllers;

use App\Domains\Tickets\Actions\GetTicketTrendsAction;
use App\Domains\Tickets\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Ticket::query()->visibleTo($user);
        $query->whereBetween('requested_at', [now()->startOfMonth(), now()->endOfMonth()]);

        $stats = [
            'total' => (clone $query)->count(),
            'open' => (clone $query)->where('status', 'open')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'closed' => (clone $query)->where('status', 'closed')->count(),
            'executed' => Ticket::query()->visibleTo($user)
                ->whereBetween('executed_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count(),
        ];

        $globalQuery = Ticket::query()->visibleTo($user);
        $globalStats = [
            'total' => (clone $globalQuery)->count(),
            'open' => (clone $globalQuery)->where('status', 'open')->count(),
            'in_progress' => (clone $globalQuery)->where('status', 'in_progress')->count(),
            'closed' => (clone $globalQuery)->where('status', 'closed')->count(),
            'executed' => (clone $globalQuery)->whereNotNull('executed_at')->count(),
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'globalStats' => $globalStats,
            'period' => now()->translatedFormat('F Y'),
            'periodStart' => now()->startOfMonth()->format('Y-m-d'),
            'periodEnd' => now()->endOfMonth()->format('Y-m-d'),
        ]);
    }

    public function trends(Request $request, GetTicketTrendsAction $action)
    {
        // Validación manual con 422 JSON: el handler global solo renderiza
        // JSON para rutas api/* (ver bootstrap/app.php), así que
        // $request->validate() redirigiría (302) en esta ruta web.
        $validator = Validator::make($request->all(), [
            'period' => 'sometimes|in:week,month,prev_month',
            'status' => 'sometimes|in:all,open,in_progress,closed',
            'company_id' => 'sometimes|integer|exists:companies,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Parámetros inválidos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        return response()->json(
            $action->execute(
                $request->user(),
                $request->input('period', 'week'),
                $request->input('status', 'all'),
                $request->input('company_id') !== null ? (int) $request->input('company_id') : null
            )
        );
    }
}
