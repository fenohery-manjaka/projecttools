<?php

namespace Tests\Feature\Modules\Tasks;

use App\Models\User;
use App\Modules\Tasks\Enums\TaskStatusCategory;
use App\Modules\Tasks\Models\Task;
use App\Modules\Tasks\Models\TaskStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Modules\Core\InteractsWithModules;
use Tests\TestCase;

class TaskStatusSettingsTest extends TestCase
{
    use InteractsWithModules, RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
        $this->admin = User::factory()->superAdmin()->create();
    }

    public function test_the_four_statuses_of_the_prototype_exist_after_migrating(): void
    {
        $this->assertSame(['À faire', 'En cours', 'Bloqué', 'Terminé'], TaskStatus::query()->ordered()->pluck('name')->all());
        $this->actingAs($this->admin)->get(route('settings.task-statuses.index'))->assertOk();
    }

    public function test_a_new_status_can_be_added_and_used(): void
    {
        $this->actingAs($this->admin)
            ->post(route('settings.task-statuses.store'), ['name' => 'En relecture', 'tone' => 'accent', 'category' => 'doing'])
            ->assertSessionHasNoErrors();

        $this->assertSame('doing', TaskStatus::query()->where('name', 'En relecture')->value('category')?->value);
    }

    public function test_the_last_todo_or_done_status_cannot_be_deleted_or_change_behaviour(): void
    {
        $done = TaskStatus::done();
        $this->actingAs($this->admin);

        $this->delete(route('settings.task-statuses.destroy', $done->id))->assertSessionHasErrors('status');
        $this->patch(route('settings.task-statuses.update', TaskStatus::todo()->id), ['name' => 'À faire', 'tone' => 'secondary', 'category' => 'doing'])
            ->assertSessionHasErrors('category');

        $this->assertModelExists($done);
    }

    public function test_a_blocked_status_can_be_deleted_its_tasks_moving_to_the_replacement(): void
    {
        $blocked = TaskStatus::firstOf(TaskStatusCategory::Blocked);
        $this->assertNotNull($blocked);
        $task = Task::factory()->blocked()->create();

        $this->actingAs($this->admin)
            ->delete(route('settings.task-statuses.destroy', $blocked->id), ['replacement_id' => TaskStatus::done()->id])
            ->assertSessionHasNoErrors();

        $task->refresh();
        $this->assertSame(TaskStatus::done()->id, $task->status_id);
        $this->assertNotNull($task->completed_at);
    }

    public function test_turning_a_status_into_done_fills_the_end_date_of_its_tasks(): void
    {
        $doing = TaskStatus::firstOf(TaskStatusCategory::Doing);
        $this->assertNotNull($doing);
        $task = Task::factory()->inCategory(TaskStatusCategory::Doing)->create();

        $this->actingAs($this->admin)
            ->patch(route('settings.task-statuses.update', $doing->id), ['name' => 'Livré', 'tone' => 'ok', 'category' => 'done'])
            ->assertSessionHasNoErrors();

        $this->assertNotNull($task->fresh()?->completed_at);
    }

    public function test_only_the_super_admin_manages_task_statuses(): void
    {
        $this->actingAs($this->userWithRole('Administrateur'))->get(route('settings.task-statuses.index'))->assertForbidden();
    }
}
