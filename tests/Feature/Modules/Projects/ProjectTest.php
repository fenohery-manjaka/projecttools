<?php

namespace Tests\Feature\Modules\Projects;

use App\Models\User;
use App\Modules\Core\Models\ActivityLog;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Modules\Core\InteractsWithModules;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use InteractsWithModules, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
    }

    public function test_creating_a_project_makes_owner_and_creator_members_and_logs_it(): void
    {
        $creator = $this->userWithRole('Responsable');
        $owner = User::factory()->create();

        $this->actingAs($creator)
            ->post(route('projects.store'), $this->payload(['owner_id' => $owner->id]))
            ->assertSessionHasNoErrors();

        $project = Project::query()->firstOrFail();

        $this->assertSame(ProjectStatus::Active, $project->status);
        $this->assertEqualsCanonicalizing([$creator->id, $owner->id], $project->members()->pluck('users.id')->all());
        $this->assertTrue(ActivityLog::query()->where('subject_id', $project->id)->where('user_id', $creator->id)->exists());
    }

    public function test_validation_rejects_an_end_before_the_start_and_an_unknown_color(): void
    {
        $this->actingAs($this->userWithRole('Responsable'))
            ->post(route('projects.store'), $this->payload(['starts_on' => '2026-11-10', 'ends_on' => '2026-11-01', 'color' => '#000000']))
            ->assertSessionHasErrors(['ends_on', 'color']);
    }

    public function test_the_list_only_shows_the_projects_the_user_belongs_to(): void
    {
        $user = $this->userWithRole('Contributeur');
        $mine = Project::factory()->withMember($user)->create();
        Project::factory()->create();

        $projects = $this->actingAs($user)->get(route('projects.index'))->viewData('page')['props']['projects'];

        $this->assertSame([$mine->id], array_column($projects, 'id'));
    }

    public function test_updating_a_project(): void
    {
        $user = $this->userWithRole('Responsable');
        $project = Project::factory()->for($user, 'owner')->withMember($user)->create();

        $this->actingAs($user)
            ->put(route('projects.update', $project), $this->payload(['name' => 'Nouveau nom', 'owner_id' => $user->id]))
            ->assertRedirect(route('projects.show', $project));

        $this->assertSame('Nouveau nom', $project->fresh()?->name);
    }

    public function test_members_can_be_added_and_removed_but_not_the_owner(): void
    {
        $manager = $this->userWithRole('Responsable');
        $project = Project::factory()->withMember($manager)->create();
        $newcomer = User::factory()->create();

        $this->actingAs($manager);

        $this->post(route('projects.members.store', $project), ['user_id' => $newcomer->id])->assertSessionHasNoErrors();
        $this->assertTrue($project->hasMember($newcomer));

        $this->delete(route('projects.members.destroy', [$project, $newcomer]))->assertSessionHasNoErrors();
        $this->assertFalse($project->hasMember($newcomer));

        $this->delete(route('projects.members.destroy', [$project, $project->owner]))->assertSessionHasErrors('member');
    }

    public function test_deleting_requires_the_delete_permission(): void
    {
        $responsable = $this->userWithRole('Responsable');
        $project = Project::factory()->withMember($responsable)->create();

        $this->actingAs($responsable)->delete(route('projects.destroy', $project))->assertForbidden();

        $this->actingAs($this->userWithRole('Administrateur'))->delete(route('projects.destroy', $project))->assertRedirect(route('projects.index'));
        $this->assertModelMissing($project);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'name' => 'Site vitrine',
            'description' => 'Présenter nos activités.',
            'objectives' => "Mettre en ligne 6 pages\nObtenir des contacts",
            'status' => ProjectStatus::Active->value,
            'owner_id' => User::factory()->create()->id,
            'color' => Project::COLORS[1],
            'starts_on' => '2026-11-01',
            'ends_on' => '2026-12-15',
            ...$overrides,
        ];
    }
}
