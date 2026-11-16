<?php

namespace Tests\Feature\Modules\Tasks;

use App\Models\User;
use App\Modules\Projects\Models\Project;
use App\Modules\Tasks\Enums\TaskPriority;
use App\Modules\Tasks\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Modules\Core\InteractsWithModules;
use Tests\TestCase;

class TaskIndexTest extends TestCase
{
    use InteractsWithModules, RefreshDatabase;

    private User $user;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
        $this->travelTo('2026-11-10');
        $this->user = $this->userWithRole('Contributeur');
        $this->project = Project::factory()->withMember($this->user)->create();
    }

    public function test_the_menu_shows_tasks_only_with_the_permission(): void
    {
        $this->assertContains('Tâches', $this->navigationTitles($this->actingAs($this->user)->get(route('projects.index'))));

        $without = $this->userWithPermissions('projects.view');
        $this->assertNotContains('Tâches', $this->navigationTitles($this->actingAs($without)->get(route('projects.index'))));
        $this->actingAs($without)->get(route('tasks.index'))->assertForbidden();
    }

    public function test_only_tasks_of_visible_and_not_archived_projects_are_listed(): void
    {
        $mine = Task::factory()->for($this->project)->create();
        Task::factory()->create();
        Task::factory()->for(Project::factory()->withMember($this->user)->archived())->create();

        $this->assertSame([$mine->id], $this->ids($this->list()));
        $this->assertSame(1, $this->list()->viewData('page')['props']['total']);
    }

    public function test_each_filter_works_alone(): void
    {
        $other = Project::factory()->withMember($this->user)->create();
        $assignee = User::factory()->create();
        $this->project->members()->attach($assignee);

        $late = Task::factory()->for($this->project)->dueOn('2026-11-05')->create(['title' => 'Logo final']);
        $soon = Task::factory()->for($other)->dueOn('2026-11-14')->priority(TaskPriority::High)->create();
        $blocked = Task::factory()->for($this->project)->blocked()->dueOn('2026-12-31')->create();
        $noDate = Task::factory()->for($this->project)->assignedTo($assignee)->create(['description' => 'Préparer le logo']);

        $this->assertEqualsCanonicalizing([$soon->id], $this->ids($this->list(['project' => $other->id])));
        $this->assertSame([$noDate->id], $this->ids($this->list(['member' => $assignee->id])));
        $this->assertSame([$soon->id], $this->ids($this->list(['priority' => 'high'])));
        $this->assertSame([$late->id], $this->ids($this->list(['due' => 'late'])));
        $this->assertSame([$soon->id], $this->ids($this->list(['due' => 'week'])));
        $this->assertSame([$blocked->id], $this->ids($this->list(['due' => 'blocked'])));
        $this->assertSame([$noDate->id], $this->ids($this->list(['due' => 'none'])));
        $this->assertEqualsCanonicalizing([$late->id, $noDate->id], $this->ids($this->list(['q' => 'logo'])));
    }

    public function test_filters_combine_and_say_so(): void
    {
        Task::factory()->for($this->project)->priority(TaskPriority::High)->create(['title' => 'Logo']);
        Task::factory()->for($this->project)->priority(TaskPriority::High)->create(['title' => 'Affiche']);

        $response = $this->list(['project' => $this->project->id, 'priority' => 'high', 'q' => 'logo']);

        $this->assertCount(1, $this->ids($response));
        $this->assertTrue($response->viewData('page')['props']['filtered']);
    }

    public function test_an_invalid_filter_comes_back_with_a_validation_error(): void
    {
        $this->actingAs($this->user)
            ->get(route('tasks.index', ['priority' => 'whatever', 'due' => 'tomorrow']))
            ->assertRedirect(route('tasks.index'))
            ->assertSessionHasErrors(['priority', 'due']);
    }

    public function test_the_list_is_sorted_by_due_date_then_priority_with_no_date_last(): void
    {
        $noDate = Task::factory()->for($this->project)->create();
        $laterHigh = Task::factory()->for($this->project)->dueOn('2026-11-20')->priority(TaskPriority::High)->create();
        $laterUrgent = Task::factory()->for($this->project)->dueOn('2026-11-20')->priority(TaskPriority::Urgent)->create();
        $first = Task::factory()->for($this->project)->dueOn('2026-11-12')->priority(TaskPriority::Low)->create();

        $this->assertSame([$first->id, $laterUrgent->id, $laterHigh->id, $noDate->id], $this->ids($this->list()));
    }

    public function test_the_number_of_queries_does_not_grow_with_the_tasks(): void
    {
        Task::factory()->for($this->project)->assignedTo($this->user)->create();
        $this->list(); // the first request also loads the user, roles and module states
        $few = $this->countQueries(fn () => $this->list());

        Task::factory()->count(15)->for($this->project)->assignedTo($this->user)->create();
        $many = $this->countQueries(fn () => $this->list());

        $this->assertSame($few, $many);
    }

    /**
     * @param  array<string, mixed>  $query
     */
    private function list(array $query = []): TestResponse
    {
        return $this->actingAs($this->user)->get(route('tasks.index', $query))->assertOk();
    }

    /**
     * @return list<int>
     */
    private function ids(TestResponse $response): array
    {
        return array_column($response->viewData('page')['props']['list']['data'], 'id');
    }

    private function countQueries(callable $callback): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $callback();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    }
}
