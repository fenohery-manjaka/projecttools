<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Projects\Enums\ProjectStatusCategory;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\ProjectStatus;
use App\Modules\Tasks\Models\Task;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Modules\Core\InteractsWithModules;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use InteractsWithModules, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_user_without_a_role_sees_explained_empty_blocks(): void
    {
        $props = $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->viewData('page')['props'];

        $this->assertSame(0, $props['summary']['count']);
        $this->assertSame([], $props['attention']);
        $this->assertSame([], $props['myTasks']);
        $this->assertSame([], $props['agenda']);
        $this->assertSame(0, $props['inProgressProjects']);
        $this->assertFalse($props['can']['createTask']);
    }

    public function test_summary_matches_blocked_and_late_attention_items(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-11-25 12:00'));
        $user = $this->userWithRole('Contributeur');
        $project = Project::factory()->withMember($user)->create();
        Task::factory()->for($project)->blocked()->create([
            'title' => 'Validation bloquée',
            'blocked_reason' => 'Attente du client',
        ]);
        Task::factory()->for($project)->blocked()->create(['title' => 'Accès bloqué']);
        Task::factory()->for($project)->dueOn('2026-11-22')->assignedTo($user)->create(['title' => 'En retard']);

        $props = $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->viewData('page')['props'];

        $this->assertSame(3, $props['summary']['count']);
        $this->assertCount(3, $props['attention']);
        $this->assertSame('Attente du client', collect($props['attention'])->firstWhere('label', 'Validation bloquée')['detail']);
        $late = collect($props['attention'])->firstWhere('label', 'En retard');
        $this->assertSame(3, $late['daysLate']);
        $this->assertSame([$user->name], $late['assignees']);
    }

    public function test_new_task_permission_controls_the_dashboard_button(): void
    {
        $allowed = $this->userWithPermissions('projects.view', 'tasks.view', 'tasks.create');
        $denied = $this->userWithPermissions('projects.view', 'tasks.view');

        $allowedProps = $this->actingAs($allowed)->get(route('dashboard'))->assertOk()->viewData('page')['props'];
        $deniedProps = $this->actingAs($denied)->get(route('dashboard'))->assertOk()->viewData('page')['props'];

        $this->assertTrue($allowedProps['can']['createTask']);
        $this->assertFalse($deniedProps['can']['createTask']);
    }

    public function test_active_project_count_excludes_archived_and_invisible_projects(): void
    {
        $user = $this->userWithRole('Contributeur');
        Project::factory()->withMember($user)->create();
        Project::factory()->withMember($user)->archived()->create();
        Project::factory()->create();

        $props = $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->viewData('page')['props'];

        $this->assertSame(1, $props['inProgressProjects']);
    }

    public function test_second_active_status_is_included_in_project_count(): void
    {
        $user = $this->userWithRole('Contributeur');
        Project::factory()->withMember($user)->create();
        $anotherActiveStatus = ProjectStatus::query()->create([
            'name' => 'En production',
            'tone' => 'ok',
            'category' => ProjectStatusCategory::Active,
            'position' => 99,
        ]);
        Project::factory()->withMember($user)->create(['status_id' => $anotherActiveStatus->id]);

        $props = $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->viewData('page')['props'];

        $this->assertSame(2, $props['inProgressProjects']);
    }

    public function test_dashboard_and_my_tasks_use_the_same_six_task_order(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-11-25 12:00'));
        $user = $this->userWithRole('Contributeur');
        $project = Project::factory()->withMember($user)->create();
        Task::factory()->for($project)->assignedTo($user)->create(['title' => 'Cette semaine', 'due_on' => '2026-11-30', 'priority' => 'urgent']);
        Task::factory()->for($project)->assignedTo($user)->create(['title' => 'En retard', 'due_on' => '2026-11-24', 'priority' => 'low']);
        Task::factory()->for($project)->assignedTo($user)->create(['title' => 'Retard urgent', 'due_on' => '2026-11-23', 'priority' => 'urgent']);
        Task::factory()->for($project)->assignedTo($user)->create(['title' => 'Aujourd’hui', 'due_on' => '2026-11-25', 'priority' => 'high']);

        $dashboardTasks = $this->actingAs($user)->get(route('dashboard'))->assertOk()->viewData('page')['props']['myTasks'];
        $myTasks = $this->actingAs($user)->get(route('tasks.mine'))->assertOk()->viewData('page')['props']['tasks'];

        $this->assertSame(array_column($myTasks, 'id'), array_column($dashboardTasks, 'id'));
        $this->assertSame('Retard urgent', $dashboardTasks[0]['title']);
        $this->assertSame('En retard', $dashboardTasks[1]['title']);
    }
}
