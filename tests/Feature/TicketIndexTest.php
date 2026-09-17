<?php

namespace Tests\Feature;

use App\Models\User;
use App\Domains\Clients\Company;
use App\Domains\Tickets\Ticket;
use App\Domains\Tickets\TicketType;
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

        $readPermission = Permission::create(['name' => 'tickets.read', 'guard_name' => 'web']);

        $role = Role::create(['name' => 'tickets.read', 'guard_name' => 'web']);
        $role->givePermissionTo($readPermission);

        $superAdmin = Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->givePermissionTo($readPermission);
    }

    private function readerUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('tickets.read');

        return $user;
    }

    private function superAdminUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        return $user;
    }

    private function makeTicket(array $attributes = []): Ticket
    {
        $company = Company::create(['name' => 'Compañía Test']);
        $type = TicketType::create(['name' => 'Soporte']);
        $creator = User::factory()->create();

        return Ticket::create(array_merge([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'company_id' => $company->id,
            'ticket_type_id' => $type->id,
            'creator_id' => $creator->id,
            'requester_id' => $creator->id,
            'requested_at' => now(),
            'description' => 'Solicitud de prueba',
        ], $attributes));
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

    public function test_index_orders_ticket_types_alphabetically(): void
    {
        TicketType::create(['name' => 'Zeta', 'is_active' => true]);
        TicketType::create(['name' => 'Alfa', 'is_active' => true]);
        TicketType::create(['name' => 'Beta', 'is_active' => true]);

        $user = $this->superAdminUser();

        $this->actingAs($user)
            ->get(route('admin.tickets.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Tickets/Index')
                ->has('ticketTypes', 3)
                ->where('ticketTypes.0.name', 'Alfa')
                ->where('ticketTypes.1.name', 'Beta')
                ->where('ticketTypes.2.name', 'Zeta'));
    }

    public function test_index_filters_by_responsible(): void
    {
        $assignee = User::factory()->create(['name' => 'Ana', 'last_name' => 'Pérez']);
        $other = User::factory()->create(['name' => 'Luis', 'last_name' => 'Gómez']);

        $assignedTicket = $this->makeTicket(['assigned_to' => $assignee->id]);
        $otherTicket = $this->makeTicket(['assigned_to' => $other->id]);

        $user = $this->superAdminUser();

        $this->actingAs($user)
            ->get(route('admin.tickets.index', ['assigned_to' => $assignee->id]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Tickets/Index')
                ->has('tickets.data', 1)
                ->where('tickets.data.0.uuid', $assignedTicket->uuid)
                ->where('assignees.0.id', $assignee->id)
                ->has('assignees', 2));
    }

    public function test_index_filters_unassigned_tickets(): void
    {
        $assignee = User::factory()->create(['name' => 'Ana', 'last_name' => 'Pérez']);

        $unassignedTicket = $this->makeTicket();
        $assignedTicket = $this->makeTicket(['assigned_to' => $assignee->id]);

        $user = $this->superAdminUser();

        $this->actingAs($user)
            ->get(route('admin.tickets.index', ['assigned_to' => 'none']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Tickets/Index')
                ->has('tickets.data', 1)
                ->where('tickets.data.0.uuid', $unassignedTicket->uuid));
    }
}