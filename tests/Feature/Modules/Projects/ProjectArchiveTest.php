<?php

namespace Tests\Feature\Modules\Projects;

use App\Modules\Core\Models\ActivityLog;
use App\Modules\Projects\Enums\ProjectStatusCategory;
use App\Modules\Projects\Models\Milestone;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\ProjectStatus;
use App\Modules\Tasks\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Modules\Core\InteractsWithModules;
use Tests\TestCase;

class ProjectArchiveTest extends TestCase
{
    use InteractsWithModules, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
    }

    public function test_the_project_list_filters_visible_projects_and_exposes_counts(): void
    {
        $user = $this->userWithRole('Responsable');
        $active = Project::factory()->withStatus(ProjectStatusCategory::Active)->withMember($user)->create();
        $paused = Project::factory()->withStatus(ProjectStatusCategory::Paused)->withMember($user)->create();
        $archived = Project::factory()->archived()->withMember($user)->create();
        Project::factory()->create();

        $page = $this->actingAs($user)->get(route('projects.index'))->viewData('page')['props'];

        $this->assertSame([$paused->id, $active->id], array_column($page['projects'], 'id'));
        $this->assertSame(2, $page['counts']['all']);
        $this->assertSame(1, $page['counts']['archived']);

        $archivedPage = $this->actingAs($user)
            ->get(route('projects.index', ['status' => 'archived']))
            ->viewData('page')['props'];

        $this->assertSame([$archived->id], array_column($archivedPage['projects'], 'id'));
        $this->assertSame('archived', $archivedPage['filter']);

        $pausedPage = $this->actingAs($user)
            ->get(route('projects.index', ['status' => ProjectStatus::firstOf(ProjectStatusCategory::Paused)->id]))
            ->viewData('page')['props'];

        $this->assertSame([$paused->id], array_column($pausedPage['projects'], 'id'));
        $this->assertSame($paused->status_id, $pausedPage['filter']);
    }

    public function test_an_invalid_project_filter_redirects_with_validation_errors(): void
    {
        $user = $this->userWithRole('Responsable');

        $this->actingAs($user)
            ->from(route('projects.index'))
            ->get(route('projects.index', ['status' => ['1']]))
            ->assertRedirect(route('projects.index'))
            ->assertSessionHasErrors('status');
    }

    public function test_a_project_can_be_archived_and_unarchived_with_activity_entries(): void
    {
        $user = $this->userWithRole('Responsable');
        $project = Project::factory()->withMember($user)->create();

        $this->actingAs($user)
            ->post(route('projects.archive', $project))
            ->assertRedirect();

        $this->assertNotNull($project->fresh()?->archived_at);
        $this->assertTrue(ActivityLog::query()->where('description', 'like', '%a archivé le projet%')->exists());

        $this->delete(route('projects.unarchive', $project))->assertRedirect();

        $this->assertNull($project->fresh()?->archived_at);
        $this->assertTrue(ActivityLog::query()->where('description', 'like', '%a désarchivé le projet%')->exists());
    }

    public function test_an_archived_project_is_read_only_for_a_responsable(): void
    {
        $user = $this->userWithRole('Responsable');
        $project = Project::factory()->archived()->withMember($user)->create();
        $task = Task::factory()->for($project)->create();
        $milestone = Milestone::factory()->for($project)->create();

        $this->actingAs($user);

        $this->put(route('projects.update', $project), [])->assertForbidden();
        $this->post(route('projects.milestones.store', $project), ['name' => 'Jalon', 'due_on' => '2026-12-01'])->assertForbidden();
        $this->post(route('tasks.store'), ['project_id' => $project->id])->assertForbidden();
        $this->put(route('tasks.update', $task), [])->assertForbidden();
        $this->delete(route('tasks.destroy', $task))->assertForbidden();
        $this->delete(route('projects.milestones.destroy', [$project, $milestone]))->assertForbidden();
    }

    public function test_a_user_without_projects_update_cannot_archive_or_unarchive(): void
    {
        $user = $this->userWithRole('Contributeur');
        $project = Project::factory()->withMember($user)->create();

        $this->actingAs($user)->post(route('projects.archive', $project))->assertForbidden();

        $project->update(['archived_at' => now()]);

        $this->delete(route('projects.unarchive', $project))->assertForbidden();
    }
}
