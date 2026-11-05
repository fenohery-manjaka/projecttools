<?php

namespace Tests\Feature\Modules\Tasks;

use App\Models\User;
use App\Modules\Projects\Models\Project;
use App\Modules\Tasks\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\Feature\Modules\Core\InteractsWithModules;
use Tests\TestCase;

class TaskPolicyTest extends TestCase
{
    use InteractsWithModules, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
    }

    public function test_a_missing_permission_refuses_even_on_a_visible_project(): void
    {
        $user = $this->userWithPermissions('projects.view');
        $task = Task::factory()->for(Project::factory()->withMember($user))->create();

        $this->assertFalse($user->can('view', $task));
        $this->assertFalse($user->can('update', $task));
        $this->assertFalse($user->can('create', [Task::class, $task->project]));
    }

    public function test_the_permission_on_an_invisible_project_refuses(): void
    {
        $user = $this->userWithPermissions('projects.view', 'tasks.view', 'tasks.create', 'tasks.update', 'tasks.delete', 'tasks.comment');
        $task = Task::factory()->create();

        foreach (['view', 'update', 'delete', 'comment'] as $ability) {
            $this->assertFalse($user->can($ability, $task), $ability);
        }
        $this->assertFalse($user->can('create', [Task::class, $task->project]));
    }

    public function test_permission_and_visible_project_allow(): void
    {
        $user = $this->userWithPermissions('projects.view', 'tasks.view', 'tasks.create', 'tasks.update', 'tasks.delete', 'tasks.comment');
        $task = Task::factory()->for(Project::factory()->withMember($user))->create();

        foreach (['view', 'update', 'delete', 'comment'] as $ability) {
            $this->assertTrue($user->can($ability, $task), $ability);
        }
        $this->assertTrue($user->can('create', [Task::class, $task->project]));
    }

    public function test_the_super_admin_is_allowed(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $task = Task::factory()->create();

        $this->assertTrue($admin->can('delete', $task));
    }

    public function test_starting_roles_get_the_permissions_of_the_lot_readme(): void
    {
        $expected = [
            'Administrateur' => ['tasks.comment', 'tasks.create', 'tasks.delete', 'tasks.update', 'tasks.view'],
            'Responsable' => ['tasks.comment', 'tasks.create', 'tasks.delete', 'tasks.update', 'tasks.view'],
            'Contributeur' => ['tasks.comment', 'tasks.create', 'tasks.update', 'tasks.view'],
            'Observateur' => ['tasks.comment', 'tasks.view'],
        ];

        foreach ($expected as $role => $permissions) {
            $granted = Role::findByName($role)->permissions->pluck('name')
                ->filter(fn (string $name): bool => str_starts_with($name, 'tasks.'))
                ->sort()->values()->all();

            $this->assertSame($permissions, $granted, $role);
        }
    }

    public function test_the_modules_page_shows_tasks_as_required_and_needing_projects(): void
    {
        $modules = $this->actingAs(User::factory()->superAdmin()->create())
            ->get(route('settings.modules.index'))->viewData('page')['props']['modules'];

        $tasks = collect($modules)->firstWhere('key', 'tasks');
        $this->assertTrue($tasks['required']);
        $this->assertSame(['Projets'], $tasks['dependencies']);
    }
}
