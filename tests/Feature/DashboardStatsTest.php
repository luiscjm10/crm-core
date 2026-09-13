<?php

namespace Tests\Feature;

use App\Domains\Clients\Company;
use App\Domains\Tickets\Ticket;
use App\Domains\Tickets\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardStatsTest extends TestCase
{
    use RefreshDatabase;

    private function makeTicket(User $user, array $attributes = []): Ticket
    {
        return Ticket::create(array_merge([
            'uuid' => (string) Str::uuid(),
            'company_id' => Company::create(['name' => 'Compañía A'])->id,
            'ticket_type_id' => TicketType::create(['name' => 'Soporte'])->id,
            'creator_id' => $user->id,
            'requester_id' => $user->id,
            'status' => 'open',
            'description' => 'Solicitud de prueba',
            'requested_at' => now(),
        ], $attributes));
    }

    public function test_executed_stats_count_tickets_executed_this_month(): void
    {
        $user = User::factory()->create();
        $this->makeTicket($user, [
            'requested_at' => now()->subMonth(),
            'status' => 'closed',
            'closed_at' => now(),
            'executed_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard')
                ->where('stats.executed', 1)
                ->where('globalStats.executed', 1));
    }

    public function test_executed_stats_exclude_tickets_not_executed(): void
    {
        $user = User::factory()->create();
        $this->makeTicket($user, [
            'status' => 'open',
            'executed_at' => null,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard')
                ->where('stats.executed', 0)
                ->where('globalStats.executed', 0));
    }
}