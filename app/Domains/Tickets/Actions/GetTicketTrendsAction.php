<?php

namespace App\Domains\Tickets\Actions;

use App\Domains\Tickets\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class GetTicketTrendsAction
{
    public function execute(User $user, string $period = 'week', string $status = 'all', ?int $companyId = null): array
    {
        [$start, $end] = $this->resolveRange($period);

        $query = Ticket::query()->visibleTo($user)
            ->whereBetween('requested_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId));

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $counts = $query->get(['requested_at'])
            ->groupBy(fn ($ticket) => $ticket->requested_at->format('Y-m-d'))
            ->map->count();

        $labels = [];
        $data = [];

        foreach (CarbonPeriod::create($start->copy()->startOfDay(), $end->copy()->startOfDay()) as $date) {
            /** @var Carbon $date */
            $key = $date->format('Y-m-d');
            $labels[] = $period === 'week'
                ? ucfirst($date->translatedFormat('D d'))
                : $date->format('j');
            $data[] = $counts->get($key, 0);
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'total' => array_sum($data),
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
            'range_label' => $this->rangeLabel($start, $end, $period),
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolveRange(string $period): array
    {
        return match ($period) {
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'prev_month' => [
                now()->subMonthNoOverflow()->startOfMonth(),
                now()->subMonthNoOverflow()->endOfMonth(),
            ],
            default => [
                now()->startOfWeek(Carbon::MONDAY),
                now()->endOfWeek(Carbon::SUNDAY),
            ],
        };
    }

    private function rangeLabel(Carbon $start, Carbon $end, string $period): string
    {
        if ($period === 'week') {
            return $start->translatedFormat('j M') . ' – ' . $end->translatedFormat('j M Y');
        }

        return $start->translatedFormat('F Y');
    }
}
