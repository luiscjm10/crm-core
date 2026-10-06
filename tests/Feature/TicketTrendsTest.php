<?php

namespace Tests\Feature;

use App\Domains\Clients\Company;
use App\Domains\Tickets\Ticket;
use App\Domains\Tickets\TicketType;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RoleAndAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class TicketTrendsTest extends TestCase
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

    private function adminWithTrendsPermission(): User
    {
        $this->seed(RoleAndAdminSeeder::class);

        $user = User::factory()->create();
        $user->givePermissionTo(Permission::findByName('tickets.view-trends', 'web'));

        return $user;
    }

    public function test_trends_requires_permission(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson(route('dashboard.trends'))
            ->assertForbidden();
    }

    public function test_trends_defaults_to_current_week_monday_to_sunday(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-17 12:00:00')); // miércoles
        $user = $this->adminWithTrendsPermission();
        $this->makeTicket($user, ['requested_at' => now()->startOfWeek(Carbon::MONDAY)]);
        $this->makeTicket($user, ['requested_at' => now()->startOfWeek(Carbon::MONDAY)->addDays(2)]);

        $response = $this->actingAs($user)->getJson(route('dashboard.trends'));

        $response->assertOk()->assertJson([
            'total' => 2,
            'start' => now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d'),
            'end' => now()->endOfWeek(Carbon::SUNDAY)->format('Y-m-d'),
        ]);
        $this->assertCount(7, $response->json('labels'));
        $this->assertCount(7, $response->json('data'));
        $this->assertSame(1, $response->json('data')[0]); // lunes
        $this->assertSame(1, $response->json('data')[2]); // miércoles

        Carbon::setTestNow();
    }

    public function test_trends_filters_by_status_and_month(): void
    {
        $user = $this->adminWithTrendsPermission();
        $this->makeTicket($user, ['status' => 'open', 'requested_at' => now()->startOfMonth()->addDay()]);
        $this->makeTicket($user, ['status' => 'closed', 'requested_at' => now()->startOfMonth()->addDay(), 'closed_at' => now()]);

        $response = $this->actingAs($user)
            ->getJson(route('dashboard.trends', ['period' => 'month', 'status' => 'closed']));

        $response->assertOk();
        $this->assertSame(1, $response->json('total'));
        $this->assertCount(now()->daysInMonth, $response->json('data'));
    }

    public function test_trends_filters_by_company(): void
    {
        $user = $this->adminWithTrendsPermission();
        $companyA = Company::create(['name' => 'Compañía A']);
        $companyB = Company::create(['name' => 'Compañía B']);
        $this->makeTicket($user, ['company_id' => $companyA->id, 'requested_at' => now()]);
        $this->makeTicket($user, ['company_id' => $companyA->id, 'requested_at' => now()]);
        $this->makeTicket($user, ['company_id' => $companyB->id, 'requested_at' => now()]);

        $this->actingAs($user)
            ->getJson(route('dashboard.trends'))
            ->assertOk()
            ->assertJson(['total' => 3]);

        $this->actingAs($user)
            ->getJson(route('dashboard.trends', ['company_id' => $companyA->id]))
            ->assertOk()
            ->assertJson(['total' => 2]);

        $this->actingAs($user)
            ->getJson(route('dashboard.trends', ['company_id' => $companyB->id]))
            ->assertOk()
            ->assertJson(['total' => 1]);
    }

    public function test_trends_rejects_invalid_params(): void
    {
        $user = $this->adminWithTrendsPermission();

        $this->actingAs($user)
            ->getJson(route('dashboard.trends', ['period' => 'year']))
            ->assertUnprocessable();

        $this->actingAs($user)
            ->getJson(route('dashboard.trends', ['status' => 'executed']))
            ->assertUnprocessable();

        $this->actingAs($user)
            ->getJson(route('dashboard.trends', ['company_id' => 999999]))
            ->assertUnprocessable();
    }
}
