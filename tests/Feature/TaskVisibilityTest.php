<?php

namespace Tests\Feature;

use App\Domains\Clients\Company;
use App\Domains\Projects\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TaskVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'super-admin', 'guard_name' => 'web']);

        $permissions = [
            'tasks.read',
            'tasks.create',
            'tasks.update',
            'tasks.assign',
            'tasks.view-all',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        $viewer = Role::create(['name' => 'viewer', 'guard_name' => 'web']);
        $viewer->givePermissionTo(['tasks.read']);

        $creator = Role::create(['name' => 'creator', 'guard_name' => 'web']);
        $creator->givePermissionTo(['tasks.read', 'tasks.create']);

        $editor = Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $editor->givePermissionTo(['tasks.read', 'tasks.create', 'tasks.update']);

        $assigner = Role::create(['name' => 'assigner', 'guard_name' => 'web']);
        $assigner->givePermissionTo(['tasks.read', 'tasks.create', 'tasks.update', 'tasks.assign']);

        $viewAll = Role::create(['name' => 'view-all-role', 'guard_name' => 'web']);
        $viewAll->givePermissionTo(['tasks.read', 'tasks.view-all']);
    }

    private function makeTask(array $attributes = []): Task
    {
        $company = Company::create(['name' => 'Compañía Test']);

        return Task::create(array_merge([
            'name' => 'Tarea de prueba',
            'company_id' => $company->id,
            'creator_id' => User::factory()->create()->id,
            'assigned_user_id' => null,
        ], $attributes));
    }

    public function test_user_without_view_all_sees_only_own_tasks(): void
    {
        $user = User::factory()->create();

        $assigned = $this->makeTask(['assigned_user_id' => $user->id]);
        $created = $this->makeTask(['creator_id' => $user->id, 'assigned_user_id' => null]);
        $other = $this->makeTask();

        $ids = Task::query()->visibleTo($user)->pluck('id');

        $this->assertTrue($ids->contains($assigned->id));
        $this->assertTrue($ids->contains($created->id));
        $this->assertFalse($ids->contains($other->id));
    }

    public function test_user_with_view_all_sees_all_tasks(): void
    {
        $user = User::factory()->create();
        $user->assignRole('view-all-role');

        $own = $this->makeTask(['creator_id' => $user->id]);
        $other = $this->makeTask();

        $ids = Task::query()->visibleTo($user)->pluck('id');

        $this->assertTrue($ids->contains($own->id));
        $this->assertTrue($ids->contains($other->id));
    }

    public function test_show_of_foreign_task_is_forbidden_without_view_all(): void
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $task = $this->makeTask();

        $this->actingAs($user)
            ->get(route('admin.companies.tasks.show', [$task->company_id, $task->id]))
            ->assertForbidden();
    }

    public function test_create_without_assign_permission_sets_creator_as_responsible(): void
    {
        $user = User::factory()->create();
        $user->assignRole('creator');

        $other = User::factory()->create();
        $company = Company::create(['name' => 'Compañía Test']);

        $this->actingAs($user)
            ->post(route('admin.tasks.store'), [
                'name' => 'Mi tarea',
                'company_id' => $company->id,
                'assigned_user_id' => $other->id,
            ])
            ->assertRedirect();

        $task = Task::where('name', 'Mi tarea')->first();
        $this->assertSame($user->id, $task->assigned_user_id);
    }

    public function test_create_with_assign_permission_respects_responsible(): void
    {
        $user = User::factory()->create();
        $user->assignRole('assigner');

        $other = User::factory()->create();
        $company = Company::create(['name' => 'Compañía Test']);

        $this->actingAs($user)
            ->post(route('admin.tasks.store'), [
                'name' => 'Tarea asignada',
                'company_id' => $company->id,
                'assigned_user_id' => $other->id,
            ])
            ->assertRedirect();

        $task = Task::where('name', 'Tarea asignada')->first();
        $this->assertSame($other->id, $task->assigned_user_id);
    }

    public function test_update_without_assign_permission_keeps_responsible(): void
    {
        $user = User::factory()->create();
        $user->assignRole('editor');

        $other = User::factory()->create();
        $task = $this->makeTask(['creator_id' => $user->id, 'assigned_user_id' => $user->id]);

        $this->actingAs($user)
            ->put(route('admin.companies.tasks.update', [$task->company_id, $task->id]), [
                'name' => 'Tarea editada',
                'assigned_user_id' => $other->id,
            ])
            ->assertRedirect();

        $task->refresh();
        $this->assertSame('Tarea editada', $task->name);
        $this->assertSame($user->id, $task->assigned_user_id);
    }

    public function test_update_with_assign_permission_changes_responsible(): void
    {
        $user = User::factory()->create();
        $user->assignRole('assigner');

        $other = User::factory()->create();
        $task = $this->makeTask(['creator_id' => $user->id, 'assigned_user_id' => $user->id]);

        $this->actingAs($user)
            ->put(route('admin.companies.tasks.update', [$task->company_id, $task->id]), [
                'name' => 'Tarea editada',
                'assigned_user_id' => $other->id,
            ])
            ->assertRedirect();

        $task->refresh();
        $this->assertSame($other->id, $task->assigned_user_id);
    }
}
