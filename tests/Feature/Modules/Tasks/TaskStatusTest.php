<?php

namespace Tests\Feature\Modules\Tasks;

use App\Models\User;
use App\Modules\Core\Models\ActivityLog;
use App\Modules\Projects\Models\Project;
use App\Modules\Tasks\Enums\TaskStatusCategory;
use App\Modules\Tasks\Models\Task;
use App\Modules\Tasks\Models\TaskStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Modules\Core\InteractsWithModules;
use Tests\TestCase;

class TaskStatusTest extends TestCase
{
    use InteractsWithModules, RefreshDatabase;

    private User $contributor;

    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
        $this->contributor = $this->userWithRole('Contributeur');
        $this->task = Task::factory()->for(Project::factory()->withMember($this->contributor))->create();
    }

    public function test_quick_change_to_done_and_back(): void
    {
        $this->actingAs($this->contributor);

        $this->patch(route('tasks.status.update', $this->task), ['status_id' => TaskStatus::done()->id])->assertSessionHasNoErrors();
        $this->assertNotNull($this->task->fresh()?->completed_at);
        $this->assertTrue(ActivityLog::query()->where('description', 'like', '%à « Terminé »%')->exists());

        $this->patch(route('tasks.status.update', $this->task), ['status_id' => TaskStatus::todo()->id]);
        $this->assertNull($this->task->fresh()?->completed_at);
    }

    public function test_blocking_needs_a_reason_which_is_cleared_when_unblocked(): void
    {
        $blocked = TaskStatus::firstOf(TaskStatusCategory::Blocked);
        $this->assertNotNull($blocked);
        $this->actingAs($this->contributor);

        $this->patch(route('tasks.status.update', $this->task), ['status_id' => $blocked->id])->assertSessionHasErrors('blocked_reason');

        $this->patch(route('tasks.status.update', $this->task), ['status_id' => $blocked->id, 'blocked_reason' => 'Attente du client'])->assertSessionHasNoErrors();
        $this->assertSame('Attente du client', $this->task->fresh()?->blocked_reason);

        $this->patch(route('tasks.status.update', $this->task), ['status_id' => TaskStatus::todo()->id]);
        $this->assertNull($this->task->fresh()?->blocked_reason);
    }

    public function test_a_status_added_in_the_settings_can_be_used(): void
    {
        $review = TaskStatus::query()->create(['name' => 'En relecture', 'tone' => 'accent', 'category' => TaskStatusCategory::Doing, 'position' => 9]);

        $this->actingAs($this->contributor)->patch(route('tasks.status.update', $this->task), ['status_id' => $review->id])->assertSessionHasNoErrors();
        $this->assertSame($review->id, $this->task->fresh()?->status_id);
    }

    public function test_an_observer_cannot_change_the_status(): void
    {
        $observer = $this->userWithRole('Observateur');
        $this->task->project->members()->attach($observer);

        $this->actingAs($observer)->patch(route('tasks.status.update', $this->task), ['status_id' => TaskStatus::done()->id])->assertForbidden();
    }
}
