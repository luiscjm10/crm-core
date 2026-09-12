<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TicketIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['name' => 'tickets.read', 'guard_name' => 'web']);
        $role->givePermissionTo(Permission::create(['name' => 'tickets.read', 'guard_name' => 'web']));
    }

    private function readerUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('tickets.read');

        return $user;
    }

    public function test_index_defaults_to_current_month_when_not_from_dashboard(): void
    {
        $user = $this->readerUser();

        $this->actingAs($user)
            ->get(route('admin.tickets.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Tickets/Index')
                ->where('filters.date_from', now()->startOfMonth()->format('Y-m-d'))
                ->where('filters.date_to', now()->endOfMonth()->format('Y-m-d'))
                ->where('filters.from', null));
    }

    public function test_index_from_dashboard_does_not_apply_month_default(): void
    {
        $user = $this->readerUser();

        $this->actingAs($user)
            ->get(route('admin.tickets.index', ['from' => 'dashboard']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Tickets/Index')
                ->where('filters.date_from', null)
                ->where('filters.date_to', null)
                ->where('filters.from', 'dashboard'));
    }

    public function test_index_respects_explicit_dates_over_default(): void
    {
        $user = $this->readerUser();

        $this->actingAs($user)
            ->get(route('admin.tickets.index', ['date_from' => '2026-01-01', 'date_to' => '2026-01-31']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Tickets/Index')
                ->where('filters.date_from', '2026-01-01')
                ->where('filters.date_to', '2026-01-31'));
    }
}