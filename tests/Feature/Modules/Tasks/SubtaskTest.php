<?php

namespace Tests\Feature\Modules\Tasks;

use App\Models\User;
use App\Modules\Projects\Models\Project;
use App\Modules\Tasks\Models\Subtask;
use App\Modules\Tasks\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Modules\Core\InteractsWithModules;
use Tests\TestCase;

class SubtaskTest extends TestCase
{
    use InteractsWithModules, RefreshDatabase;

    private User $user;

    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
        $this->user = $this->userWithRole('Contributeur');
        $this->task = Task::factory()->for(Project::factory()->withMember($this->user))->create();
    }

    public function test_adding_subtasks_keeps_their_order_on_the_task_page(): void
    {
        $this->actingAs($this->user);

        $this->post(route('tasks.subtasks.store', $this->task), ['title' => 'Maquette'])->assertSessionHasNoErrors();
        $this->post(route('tasks.subtasks.store', $this->task), ['title' => 'Relecture'])->assertSessionHasNoErrors();
        $this->post(route('tasks.subtasks.store', $this->task), ['title' => ''])->assertSessionHasErrors('title');

        $list = $this->get(route('tasks.show', $this->task))->viewData('page')['props']['task']['subtask_list'];
        $this->assertSame(['Maquette', 'Relecture'], array_column($list, 'title'));
    }

    public function test_ticking_unticking_and_renaming(): void
    {
        $subtask = Subtask::factory()->for($this->task)->create();
        $this->actingAs($this->user);

        $this->patch(route('tasks.subtasks.update', [$this->task, $subtask]), ['done' => true]);
        $this->assertNotNull($subtask->fresh()?->done_at);

        $this->patch(route('tasks.subtasks.update', [$this->task, $subtask]), ['done' => false]);
        $this->assertNull($subtask->fresh()?->done_at);

        $this->patch(route('tasks.subtasks.update', [$this->task, $subtask]), ['title' => 'Nouveau texte']);
        $this->assertSame('Nouveau texte', $subtask->fresh()?->title);
    }

    public function test_deleting(): void
    {
        $subtask = Subtask::factory()->for($this->task)->create();

        $this->actingAs($this->user)->delete(route('tasks.subtasks.destroy', [$this->task, $subtask]))->assertSessionHasNoErrors();

        $this->assertModelMissing($subtask);
    }

    public function test_the_counter_is_right_in_the_list_and_on_the_board(): void
    {
        Subtask::factory()->count(2)->done()->for($this->task)->create();
        Subtask::factory()->count(3)->for($this->task)->create();
        $this->actingAs($this->user);

        $row = $this->get(route('tasks.index'))->viewData('page')['props']['list']['data'][0];
        $card = $this->get(route('tasks.index', ['view' => 'board']))->viewData('page')['props']['board'][0];

        $this->assertSame(['done' => 2, 'total' => 5], $row['subtasks']);
        $this->assertSame(['done' => 2, 'total' => 5], $card['subtasks']);
    }

    public function test_a_subtask_of_another_task_is_not_found(): void
    {
        $other = Subtask::factory()->create();

        $this->actingAs($this->user)->patch(route('tasks.subtasks.update', [$this->task, $other]), ['done' => true])->assertNotFound();
    }

    public function test_an_observer_cannot_change_subtasks(): void
    {
        $observer = $this->userWithRole('Observateur');
        $this->task->project->members()->attach($observer);
        $subtask = Subtask::factory()->for($this->task)->create();

        $this->actingAs($observer);
        $this->post(route('tasks.subtasks.store', $this->task), ['title' => 'X'])->assertForbidden();
        $this->patch(route('tasks.subtasks.update', [$this->task, $subtask]), ['done' => true])->assertForbidden();
        $this->delete(route('tasks.subtasks.destroy', [$this->task, $subtask]))->assertForbidden();
    }
}
