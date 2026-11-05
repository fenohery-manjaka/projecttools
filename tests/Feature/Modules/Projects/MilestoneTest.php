<?php

namespace Tests\Feature\Modules\Projects;

use App\Models\User;
use App\Modules\Core\Models\ActivityLog;
use App\Modules\Projects\Models\Milestone;
use App\Modules\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Modules\Core\InteractsWithModules;
use Tests\TestCase;

class MilestoneTest extends TestCase
{
    use InteractsWithModules, RefreshDatabase;

    private User $manager;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
        $this->manager = $this->userWithRole('Responsable');
        $this->project = Project::factory()->withMember($this->manager)->create();
    }

    public function test_adding_a_milestone_logs_it_and_the_page_lists_them_by_date(): void
    {
        Milestone::factory()->for($this->project)->create(['name' => 'Mise en ligne', 'due_on' => '2026-12-20']);

        $this->actingAs($this->manager)
            ->post(route('projects.milestones.store', $this->project), ['name' => 'Maquette validée', 'due_on' => '2026-11-12'])
            ->assertSessionHasNoErrors();

        $this->assertTrue(ActivityLog::query()->where('description', 'like', '%jalon « Maquette validée »%')->exists());

        $milestones = $this->actingAs($this->manager)->get(route('projects.show', $this->project))->viewData('page')['props']['project']['milestones'];
        $this->assertSame(['Maquette validée', 'Mise en ligne'], array_column($milestones, 'name'));
    }

    public function test_name_and_date_are_required(): void
    {
        $this->actingAs($this->manager)
            ->post(route('projects.milestones.store', $this->project), ['name' => str_repeat('a', 121)])
            ->assertSessionHasErrors(['name', 'due_on']);
    }

    public function test_reaching_fills_the_date_and_unreaching_clears_it(): void
    {
        $milestone = Milestone::factory()->for($this->project)->create();
        $this->actingAs($this->manager);

        $this->patch(route('projects.milestones.update', [$this->project, $milestone]), ['reached' => true])->assertSessionHasNoErrors();
        $this->assertNotNull($milestone->fresh()?->reached_at);
        $this->assertTrue(ActivityLog::query()->where('description', 'like', '%a atteint le jalon%')->exists());

        $this->patch(route('projects.milestones.update', [$this->project, $milestone]), ['reached' => false]);
        $this->assertNull($milestone->fresh()?->reached_at);
    }

    public function test_renaming_moving_and_deleting(): void
    {
        $milestone = Milestone::factory()->for($this->project)->create();
        $this->actingAs($this->manager);

        $this->patch(route('projects.milestones.update', [$this->project, $milestone]), ['name' => 'Recette', 'due_on' => '2027-01-15'])->assertSessionHasNoErrors();
        $this->assertSame('Recette', $milestone->fresh()?->name);
        $this->assertSame('2027-01-15', $milestone->fresh()?->due_on->toDateString());

        $this->delete(route('projects.milestones.destroy', [$this->project, $milestone]))->assertSessionHasNoErrors();
        $this->assertModelMissing($milestone);
    }

    public function test_a_past_unreached_milestone_is_overdue(): void
    {
        $this->travelTo('2026-11-10');

        $this->assertTrue(Milestone::factory()->make(['due_on' => '2026-11-09'])->is_overdue);
        $this->assertFalse(Milestone::factory()->make(['due_on' => '2026-11-10'])->is_overdue);
        $this->assertFalse(Milestone::factory()->reached()->make(['due_on' => '2026-11-01'])->is_overdue);
    }

    public function test_a_milestone_of_another_project_is_not_found(): void
    {
        $other = Milestone::factory()->create();

        $this->actingAs($this->manager)
            ->patch(route('projects.milestones.update', [$this->project, $other]), ['reached' => true])
            ->assertNotFound();
    }

    public function test_an_observer_sees_the_milestones_but_cannot_manage_them(): void
    {
        $observer = $this->userWithRole('Observateur');
        $this->project->members()->attach($observer);
        $milestone = Milestone::factory()->for($this->project)->create();

        $props = $this->actingAs($observer)->get(route('projects.show', $this->project))->viewData('page')['props'];
        $this->assertCount(1, $props['project']['milestones']);
        $this->assertFalse($props['can']['update']);

        $this->post(route('projects.milestones.store', $this->project), ['name' => 'X', 'due_on' => '2026-12-01'])->assertForbidden();
        $this->patch(route('projects.milestones.update', [$this->project, $milestone]), ['reached' => true])->assertForbidden();
        $this->delete(route('projects.milestones.destroy', [$this->project, $milestone]))->assertForbidden();
    }

    public function test_a_manager_outside_the_project_is_refused(): void
    {
        $outsider = $this->userWithPermissions('projects.view', 'projects.update');

        $this->actingAs($outsider)
            ->post(route('projects.milestones.store', $this->project), ['name' => 'X', 'due_on' => '2026-12-01'])
            ->assertForbidden();
    }
}
