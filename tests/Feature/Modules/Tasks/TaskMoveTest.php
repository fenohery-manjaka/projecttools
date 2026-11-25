<?php

namespace Tests\Feature\Modules\Tasks;

use App\Models\User;
use App\Modules\Projects\Models\Project;
use App\Modules\Tasks\Enums\TaskStatusCategory;
use App\Modules\Tasks\Models\Task;
use App\Modules\Tasks\Models\TaskStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Modules\Core\InteractsWithModules;
use Tests\TestCase;

class TaskMoveTest extends TestCase
{
    use InteractsWithModules, RefreshDatabase;

    private User $user;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
        $this->user = $this->userWithRole('Contributeur');
        $this->project = Project::factory()->withMember($this->user)->create();
    }

    public function test_the_board_view_sends_cards_and_columns_in_the_order_of_the_settings(): void
    {
        Task::factory()->for($this->project)->create();

        $props = $this->actingAs($this->user)->get(route('tasks.index', ['view' => 'board']))->assertOk()->viewData('page')['props'];

        $this->assertSame('board', $props['view']);
        $this->assertCount(1, $props['board']);
        $this->assertSame(['À faire', 'En cours', 'Bloqué', 'Terminé'], array_column($props['statuses'], 'name'));
    }

    public function test_moving_changes_the_status_and_renumbers_the_column(): void
    {
        $doing = $this->firstStatus(TaskStatusCategory::Doing);
        [$a, $b] = Task::factory()->count(2)->for($this->project)->inCategory(TaskStatusCategory::Doing)->sequence(['position' => 0], ['position' => 1])->create();
        $moved = Task::factory()->for($this->project)->create();

        $this->actingAs($this->user)
            ->patch(route('tasks.move', $moved), ['status_id' => $doing->id, 'position' => 1])
            ->assertSessionHasNoErrors();

        $this->assertSame([$a->id, $moved->id, $b->id], Task::query()->where('status_id', $doing->id)->orderBy('position')->pluck('id')->all());
        $this->assertSame([0, 1, 2], Task::query()->where('status_id', $doing->id)->orderBy('position')->pluck('position')->all());
    }

    public function test_moving_inside_a_column_reorders_it(): void
    {
        [$a, $b, $c] = Task::factory()->count(3)->for($this->project)->sequence(['position' => 0], ['position' => 1], ['position' => 2])->create();

        $this->actingAs($this->user)->patch(route('tasks.move', $c), ['status_id' => $c->status_id, 'position' => 0]);

        $this->assertSame([$c->id, $a->id, $b->id], Task::query()->orderBy('position')->pluck('id')->all());
    }

    public function test_dropping_in_blocked_needs_a_reason_even_when_calling_the_server_directly(): void
    {
        $blocked = $this->firstStatus(TaskStatusCategory::Blocked);
        $task = Task::factory()->for($this->project)->create();

        $this->actingAs($this->user)->patch(route('tasks.move', $task), ['status_id' => $blocked->id, 'position' => 0])->assertSessionHasErrors('blocked_reason');
        $this->assertNotSame($blocked->id, $task->fresh()?->status_id);

        $this->actingAs($this->user)->patch(route('tasks.move', $task), ['status_id' => $blocked->id, 'position' => 0, 'blocked_reason' => 'Attente du devis'])->assertSessionHasNoErrors();
        $this->assertSame('Attente du devis', $task->fresh()?->blocked_reason);
    }

    public function test_reordering_inside_the_blocked_column_keeps_the_reason(): void
    {
        $task = Task::factory()->for($this->project)->blocked()->create();

        $this->actingAs($this->user)->patch(route('tasks.move', $task), ['status_id' => $task->status_id, 'position' => 0])->assertSessionHasNoErrors();

        $this->assertSame('En attente d’une validation.', $task->fresh()?->blocked_reason);
    }

    public function test_dropping_in_done_fills_the_end_date(): void
    {
        $task = Task::factory()->for($this->project)->create();

        $this->actingAs($this->user)->patch(route('tasks.move', $task), ['status_id' => TaskStatus::done()->id, 'position' => 0]);

        $this->assertNotNull($task->fresh()?->completed_at);
    }

    public function test_an_observer_cannot_move_cards(): void
    {
        $observer = $this->userWithRole('Observateur');
        $this->project->members()->attach($observer);
        $task = Task::factory()->for($this->project)->create();

        $this->assertFalse($this->actingAs($observer)->get(route('tasks.index', ['view' => 'board']))->viewData('page')['props']['can']['update']);
        $this->actingAs($observer)->patch(route('tasks.move', $task), ['status_id' => TaskStatus::done()->id, 'position' => 0])->assertForbidden();
    }

    private function firstStatus(TaskStatusCategory $category): TaskStatus
    {
        $status = TaskStatus::firstOf($category);
        $this->assertNotNull($status);

        return $status;
    }
}
