<?php

namespace Tests\Feature\Modules\Tasks;

use App\Models\User;
use App\Modules\Core\Models\ActivityLog;
use App\Modules\Projects\Models\Milestone;
use App\Modules\Projects\Models\Project;
use App\Modules\Tasks\Enums\TaskStatusCategory;
use App\Modules\Tasks\Models\Task;
use App\Modules\Tasks\Models\TaskStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Modules\Core\InteractsWithModules;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use InteractsWithModules, RefreshDatabase;

    private User $contributor;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
        $this->contributor = $this->userWithRole('Contributeur');
        $this->project = Project::factory()->withMember($this->contributor)->create();
    }

    public function test_creating_a_task_from_a_page_comes_back_to_it_and_logs_it(): void
    {
        $milestone = Milestone::factory()->for($this->project)->create();

        $this->actingAs($this->contributor)
            ->from(route('projects.show', $this->project))
            ->post(route('tasks.store'), $this->payload(['milestone_id' => $milestone->id, 'assignee_ids' => [$this->contributor->id]]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('projects.show', $this->project));

        $task = Task::query()->firstOrFail();
        $this->assertSame($this->contributor->id, $task->created_by);
        $this->assertSame([$this->contributor->id], $task->assignees()->pluck('users.id')->all());
        $this->assertSame($milestone->id, $task->milestone_id);
        $this->assertTrue(ActivityLog::query()->where('description', 'like', '%a créé la tâche%')->exists());
    }

    public function test_there_is_no_create_page_and_the_project_page_loads_the_dialog_options_on_demand(): void
    {
        $this->actingAs($this->contributor)->get('/tasks/create')->assertNotFound();

        $page = $this->actingAs($this->contributor)->get(route('projects.show', $this->project))->viewData('page');
        $this->assertTrue($page['props']['can']['createTask']);
        $this->assertArrayNotHasKey('taskForm', $page['props']);

        $this->actingAs($this->contributor)
            ->get(route('projects.show', $this->project), [
                'X-Inertia' => 'true',
                'X-Inertia-Version' => (string) $page['version'],
                'X-Inertia-Partial-Component' => 'projects/Show',
                'X-Inertia-Partial-Data' => 'taskForm',
            ])
            ->assertJsonPath('props.taskForm.defaults.project_id', $this->project->id)
            ->assertJsonCount(2, 'props.taskForm.projects.0.members')
            ->assertJsonCount(4, 'props.taskForm.statuses');
    }

    public function test_an_assignee_outside_the_project_or_a_milestone_of_another_project_is_refused(): void
    {
        $this->actingAs($this->contributor)
            ->post(route('tasks.store'), $this->payload([
                'assignee_ids' => [User::factory()->create()->id],
                'milestone_id' => Milestone::factory()->create()->id,
            ]))
            ->assertSessionHasErrors(['assignee_ids', 'milestone_id']);
    }

    public function test_a_blocked_status_needs_a_reason_on_the_server(): void
    {
        $blocked = TaskStatus::firstOf(TaskStatusCategory::Blocked);

        $this->actingAs($this->contributor)
            ->post(route('tasks.store'), $this->payload(['status_id' => $blocked?->id]))
            ->assertSessionHasErrors('blocked_reason');
    }

    public function test_creating_in_a_project_one_cannot_see_is_refused(): void
    {
        $this->actingAs($this->contributor)
            ->post(route('tasks.store'), $this->payload(['project_id' => Project::factory()->create()->id]))
            ->assertForbidden();
    }

    public function test_the_task_page_shows_the_task_and_the_edit_form_is_prefilled(): void
    {
        $task = Task::factory()->for($this->project)->blocked()->create(['description' => 'Texte existant']);

        $props = $this->actingAs($this->contributor)->get(route('tasks.show', $task))->assertOk()->viewData('page')['props'];
        $this->assertSame('En attente d’une validation.', $props['task']['blocked_reason']);
        $this->assertTrue($props['can']['update']);
        $this->assertFalse($props['can']['delete']);

        $edit = $this->actingAs($this->contributor)->get(route('tasks.edit', $task))->assertOk()->viewData('page')['props'];
        $this->assertSame('Texte existant', $edit['task']['description']);
        $this->assertSame('En attente d’une validation.', $edit['task']['blocked_reason']);
    }

    public function test_updating_to_done_fills_the_end_date_and_leaving_it_clears_it(): void
    {
        $task = Task::factory()->for($this->project)->create();
        $this->actingAs($this->contributor);

        $this->put(route('tasks.update', $task), $this->payload(['title' => 'Renommée', 'status_id' => TaskStatus::done()->id]))
            ->assertRedirect(route('tasks.show', $task));
        $task->refresh();
        $this->assertSame('Renommée', $task->title);
        $this->assertNotNull($task->completed_at);

        $this->put(route('tasks.update', $task), $this->payload(['status_id' => TaskStatus::todo()->id]));
        $this->assertNull($task->fresh()?->completed_at);
    }

    public function test_deleting_needs_the_delete_permission_and_is_logged(): void
    {
        $task = Task::factory()->for($this->project)->create();

        $this->actingAs($this->contributor)->delete(route('tasks.destroy', $task))->assertForbidden();

        $manager = $this->userWithRole('Responsable');
        $this->actingAs($manager)->delete(route('tasks.destroy', $task))->assertRedirect(route('projects.show', $this->project));
        $this->assertModelMissing($task);
        $this->assertTrue(ActivityLog::query()->where('description', 'like', '%a supprimé la tâche%')->exists());
    }

    public function test_an_observer_reads_but_cannot_change_and_an_outsider_cannot_read(): void
    {
        $observer = $this->userWithRole('Observateur');
        $this->project->members()->attach($observer);
        $task = Task::factory()->for($this->project)->create();

        $props = $this->actingAs($observer)->get(route('tasks.show', $task))->assertOk()->viewData('page')['props'];
        $this->assertFalse($props['can']['update']);
        $this->actingAs($observer)->get(route('tasks.edit', $task))->assertForbidden();
        $this->actingAs($observer)->put(route('tasks.update', $task), $this->payload())->assertForbidden();
        $this->actingAs($observer)->post(route('tasks.store'), $this->payload())->assertForbidden();

        $this->actingAs($this->userWithRole('Contributeur'))->get(route('tasks.show', $task))->assertForbidden();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'title' => 'Préparer la maquette',
            'project_id' => $this->project->id,
            'milestone_id' => null,
            'status_id' => TaskStatus::todo()->id,
            'priority' => 'high',
            'assignee_ids' => [],
            'due_on' => '2026-11-20',
            'estimate_hours' => '4',
            'description' => 'Trois écrans.',
            ...$overrides,
        ];
    }
}
