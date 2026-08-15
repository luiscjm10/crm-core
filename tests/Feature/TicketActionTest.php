<?php

namespace Tests\Feature;

use App\Domains\Clients\Company;
use App\Domains\Tickets\Ticket;
use App\Domains\Tickets\TicketType;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TicketActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
        Role::create(['name' => 'read-only', 'guard_name' => 'web']);

        foreach (['tickets.change-type', 'tickets.take', 'tickets.reopen'] as $permission) {
            $role = Role::create(['name' => $permission, 'guard_name' => 'web']);
            $role->givePermissionTo(Permission::create(['name' => $permission, 'guard_name' => 'web']));
        }
    }

    private function companyWithTypes(): array
    {
        $company = Company::create(['name' => 'Compañía Test']);
        $typeA = TicketType::create(['name' => 'Soporte']);
        $typeB = TicketType::create(['name' => 'Desarrollo']);
        $company->ticketTypes()->attach([$typeA->id, $typeB->id]);

        return [$company, $typeA, $typeB];
    }

    private function makeTicket(array $attributes = [], ?Company $company = null, ?TicketType $type = null): Ticket
    {
        $company = $company ?? Company::create(['name' => 'Compañía Test']);
        $type = $type ?? TicketType::create(['name' => 'Soporte']);
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

    public function test_change_type_updates_ticket_and_adds_system_comment(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');

        [$company, $typeA, $typeB] = $this->companyWithTypes();
        $ticket = $this->makeTicket(['ticket_type_id' => $typeA->id], $company, $typeA);

        $this->actingAs($superAdmin)
            ->patch(route('admin.tickets.type', $ticket->uuid), ['ticket_type_id' => $typeB->id])
            ->assertRedirect();

        $this->assertSame($typeB->id, $ticket->fresh()->ticket_type_id);

        $comment = Comment::where('commentable_id', $ticket->id)->where('commentable_type', Ticket::class)->first();
        $this->assertNotNull($comment);
        $this->assertTrue((bool) $comment->is_system);
        $this->assertStringContainsString($typeA->name, $comment->content);
        $this->assertStringContainsString($typeB->name, $comment->content);
    }

    public function test_change_type_is_forbidden_without_permission(): void
    {
        $user = User::factory()->create();
        $user->assignRole('read-only');

        [, , $typeB] = $this->companyWithTypes();
        $ticket = $this->makeTicket();

        $this->actingAs($user)
            ->patch(route('admin.tickets.type', $ticket->uuid), ['ticket_type_id' => $typeB->id])
            ->assertForbidden();
    }

    public function test_take_assigns_and_adds_system_comment(): void
    {
        $user = User::factory()->create();
        $user->assignRole('tickets.take');

        $ticket = $this->makeTicket(['assigned_to' => null]);

        $this->actingAs($user)
            ->patch(route('admin.tickets.take', $ticket->uuid))
            ->assertRedirect();

        $this->assertSame($user->id, $ticket->fresh()->assigned_to);

        $comment = Comment::where('commentable_id', $ticket->id)->where('commentable_type', Ticket::class)->first();
        $this->assertNotNull($comment);
        $this->assertTrue((bool) $comment->is_system);
        $this->assertStringContainsString('tomado por ' . $user->name, $comment->content);
    }

    public function test_reopen_returns_closed_ticket_to_open(): void
    {
        $user = User::factory()->create();
        $user->assignRole('tickets.reopen');

        $ticket = $this->makeTicket(['status' => 'closed', 'closed_at' => now()]);

        $this->actingAs($user)
            ->patch(route('admin.tickets.reopen', $ticket->uuid))
            ->assertRedirect();

        $ticket->refresh();
        $this->assertSame('open', $ticket->status);
        $this->assertNull($ticket->closed_at);

        $comment = Comment::where('commentable_id', $ticket->id)->where('commentable_type', Ticket::class)->first();
        $this->assertNotNull($comment);
        $this->assertTrue((bool) $comment->is_system);
        $this->assertStringContainsString('reabierto por ' . $user->name, $comment->content);
    }

    public function test_reopen_is_rejected_for_non_closed_ticket(): void
    {
        $user = User::factory()->create();
        $user->assignRole('tickets.reopen');

        $ticket = $this->makeTicket(['status' => 'open']);

        $this->actingAs($user)
            ->patch(route('admin.tickets.reopen', $ticket->uuid))
            ->assertStatus(422);
    }
}
