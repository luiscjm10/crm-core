<?php

namespace Tests\Feature;

use App\Domains\Clients\Company;
use App\Domains\Tickets\Ticket;
use App\Domains\Tickets\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TicketVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
        Role::create(['name' => 'view-all', 'guard_name' => 'web']);
        Role::create(['name' => 'agente', 'guard_name' => 'web']);

        Permission::create(['name' => 'tickets.view-all', 'guard_name' => 'web']);
        Role::findByName('view-all', 'web')->givePermissionTo('tickets.view-all');
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
            'description' => 'Solicitud de prueba',
        ], $attributes));
    }

    public function test_super_admin_sees_all_tickets(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');

        $ticket = $this->makeTicket();

        $this->assertTrue(Ticket::query()->visibleTo($superAdmin)->whereKey($ticket->id)->exists());
    }

    public function test_view_all_user_sees_company_tickets(): void
    {
        $company = Company::create(['name' => 'Compañía A']);
        $helper = User::factory()->create(['company_id' => $company->id]);
        $helper->assignRole('view-all');
        $helper->companies()->attach($company);

        $ticket = $this->makeTicket(['company_id' => $company->id]);

        $this->assertTrue(Ticket::query()->visibleTo($helper)->whereKey($ticket->id)->exists());
    }

    public function test_regular_user_sees_only_own_tickets(): void
    {
        $user = User::factory()->create();

        $own = $this->makeTicket(['creator_id' => $user->id, 'requester_id' => $user->id]);
        $other = $this->makeTicket();

        $ids = Ticket::query()->visibleTo($user)->pluck('id');

        $this->assertTrue($ids->contains($own->id));
        $this->assertFalse($ids->contains($other->id));
    }

    public function test_restricted_user_does_not_see_tickets_of_other_types(): void
    {
        $allowedType = TicketType::create(['name' => 'Soporte']);
        $restrictedType = TicketType::create(['name' => 'Desarrollo']);

        $helper = User::factory()->create();
        $helper->assignRole('agente');
        $helper->ticketTypes()->attach($allowedType);

        $allowed = $this->makeTicket([
            'ticket_type_id' => $allowedType->id,
            'creator_id' => $helper->id,
            'requester_id' => $helper->id,
        ]);
        $restricted = $this->makeTicket(['ticket_type_id' => $restrictedType->id]);

        $ids = Ticket::query()->visibleTo($helper)->pluck('id');

        $this->assertTrue($ids->contains($allowed->id));
        $this->assertFalse($ids->contains($restricted->id));
    }

    public function test_restricted_user_sees_assigned_ticket_of_restricted_type(): void
    {
        $restrictedType = TicketType::create(['name' => 'Desarrollo']);
        $allowedType = TicketType::create(['name' => 'Soporte']);

        $helper = User::factory()->create();
        $helper->assignRole('agente');
        $helper->ticketTypes()->attach($allowedType);

        $assigned = $this->makeTicket([
            'ticket_type_id' => $restrictedType->id,
            'assigned_to' => $helper->id,
        ]);

        $this->assertTrue(Ticket::query()->visibleTo($helper)->whereKey($assigned->id)->exists());
    }

    public function test_view_all_user_does_not_see_assigned_ticket_outside_their_company_scope(): void
    {
        $company = Company::create(['name' => 'Compañía A']);
        $otherCompany = Company::create(['name' => 'Compañía B']);
        $restrictedType = TicketType::create(['name' => 'Desarrollo']);
        $allowedType = TicketType::create(['name' => 'Soporte']);

        $helper = User::factory()->create(['company_id' => $company->id]);
        $helper->assignRole('view-all');
        $helper->companies()->attach($company);
        $helper->ticketTypes()->attach($allowedType);

        $other = $this->makeTicket([
            'company_id' => $otherCompany->id,
            'ticket_type_id' => $restrictedType->id,
            'assigned_to' => $helper->id,
        ]);

        $this->assertFalse(Ticket::query()->visibleTo($helper)->whereKey($other->id)->exists());
    }
}
