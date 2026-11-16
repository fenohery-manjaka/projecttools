<?php

namespace Tests\Feature\Modules\Tasks;

use App\Models\User;
use App\Modules\Projects\Models\Project;
use App\Modules\Tasks\Database\Factories\TaskFactory;
use App\Modules\Tasks\Enums\TaskPriority;
use App\Modules\Tasks\Models\Task;
use App\Modules\Tasks\Models\TaskStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Modules\Core\InteractsWithModules;
use Tests\TestCase;

class MyTasksTest extends TestCase
{
    use InteractsWithModules, RefreshDatabase;

    private User $user;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
        $this->travelTo('2026-11-10 09:00');
        $this->user = $this->userWithRole('Contributeur');
        $this->project = Project::factory()->withMember($this->user)->create();
    }

    public function test_only_my_open_tasks_appear_in_the_right_group(): void
    {
        $late = $this->mine()->dueOn('2026-11-09')->create();
        $today = $this->mine()->dueOn('2026-11-10')->create();
        $week = $this->mine()->dueOn('2026-11-17')->create();
        $later = $this->mine()->dueOn('2026-11-18')->create();
        $none = $this->mine()->create();
        $this->mine()->done()->create();
        Task::factory()->for($this->project)->create();

        $groups = $this->page()['groups'];

        $this->assertSame(['late', 'today', 'week', 'later', 'none'], array_column($groups, 'key'));
        $this->assertSame(
            [[$late->id], [$today->id], [$week->id], [$later->id], [$none->id]],
            array_map(fn (array $group): array => array_column($group['tasks'], 'id'), $groups),
        );
        $this->assertSame(5, $this->page()['openCount']);
    }

    public function test_empty_groups_are_not_sent(): void
    {
        $this->mine()->dueOn('2026-11-10')->create();

        $this->assertSame(['today'], array_column($this->page()['groups'], 'key'));
    }

    public function test_inside_a_group_tasks_follow_priority_then_due_date(): void
    {
        $normalSoon = $this->mine()->dueOn('2026-11-12')->create();
        $urgentLater = $this->mine()->dueOn('2026-11-15')->priority(TaskPriority::Urgent)->create();
        $normalLater = $this->mine()->dueOn('2026-11-14')->create();

        $week = collect($this->page()['groups'])->firstWhere('key', 'week');

        $this->assertSame([$urgentLater->id, $normalSoon->id, $normalLater->id], array_column($week['tasks'], 'id'));
    }

    public function test_ticking_finishes_the_task_which_moves_to_recently_done_and_unticking_reopens_it(): void
    {
        $task = $this->mine()->create();
        $statuses = $this->page()['tickStatuses'];

        $this->actingAs($this->user)->patch(route('tasks.status.update', $task), ['status_id' => $statuses['done']]);
        $page = $this->page();
        $this->assertSame([], $page['groups']);
        $this->assertSame([$task->id], array_column($page['recentlyDone'], 'id'));

        $this->actingAs($this->user)->patch(route('tasks.status.update', $task), ['status_id' => $statuses['todo']]);
        $this->assertSame(TaskStatus::todo()->id, $task->fresh()?->status_id);
        $this->assertSame([], $this->page()['recentlyDone']);
    }

    public function test_my_load_counts_open_tasks_by_priority_and_remaining_estimate(): void
    {
        $this->mine()->priority(TaskPriority::Urgent)->create(['estimate_hours' => 4]);
        $this->mine()->priority(TaskPriority::Urgent)->create(['estimate_hours' => 2.5]);
        $this->mine()->create(['estimate_hours' => null]);
        $this->mine()->done()->create(['estimate_hours' => 10]);

        $load = $this->page()['load'];

        $this->assertSame(2, collect($load['byPriority'])->firstWhere('value', 'urgent')['count']);
        $this->assertSame(1, collect($load['byPriority'])->firstWhere('value', 'normal')['count']);
        $this->assertEquals(6.5, $load['remainingHours']);
    }

    public function test_an_observer_sees_the_page_without_boxes(): void
    {
        $observer = $this->userWithRole('Observateur');

        $props = $this->actingAs($observer)->get(route('tasks.mine'))->assertOk()->viewData('page')['props'];

        $this->assertNull($props['tickStatuses']);
    }

    public function test_the_menu_entry_and_the_page_need_the_view_permission(): void
    {
        $this->assertContains('Mes tâches', $this->navigationTitles($this->actingAs($this->user)->get(route('tasks.mine'))));

        $this->actingAs($this->userWithPermissions('projects.view'))->get(route('tasks.mine'))->assertForbidden();
    }

    private function mine(): TaskFactory
    {
        return Task::factory()->for($this->project)->assignedTo($this->user);
    }

    /**
     * @return array<string, mixed>
     */
    private function page(): array
    {
        return $this->actingAs($this->user)->get(route('tasks.mine'))->assertOk()->viewData('page')['props'];
    }
}
