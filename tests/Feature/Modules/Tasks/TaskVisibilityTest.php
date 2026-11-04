<?php

namespace Tests\Feature\Modules\Tasks;

use App\Modules\Projects\Models\Project;
use App\Modules\Tasks\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Modules\Core\InteractsWithModules;
use Tests\TestCase;

class TaskVisibilityTest extends TestCase
{
    use InteractsWithModules, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
    }

    public function test_only_tasks_of_visible_and_not_archived_projects_are_visible(): void
    {
        $user = $this->userWithRole('Contributeur');
        $visible = Task::factory()->for(Project::factory()->withMember($user))->create();
        Task::factory()->create();
        Task::factory()->for(Project::factory()->withMember($user)->archived())->create();

        $this->assertSame([$visible->id], Task::query()->visibleTo($user)->pluck('id')->all());
    }

    public function test_view_all_sees_every_project_but_still_not_the_archived_ones(): void
    {
        $user = $this->userWithRole('Responsable');
        Task::factory()->count(2)->create();
        Task::factory()->for(Project::factory()->archived())->create();

        $this->assertSame(2, Task::query()->visibleTo($user)->count());
    }

    public function test_a_task_is_late_only_when_its_due_date_is_past_and_it_is_not_done(): void
    {
        $this->travelTo('2026-11-10');

        $this->assertTrue(Task::factory()->dueOn('2026-11-09')->create()->is_late);
        $this->assertFalse(Task::factory()->dueOn('2026-11-10')->create()->is_late);
        $this->assertFalse(Task::factory()->done()->dueOn('2026-11-01')->create()->is_late);
        $this->assertFalse(Task::factory()->create()->is_late);

        $this->assertSame(1, Task::query()->late()->count());
    }
}
