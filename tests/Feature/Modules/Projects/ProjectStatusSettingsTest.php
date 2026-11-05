<?php

namespace Tests\Feature\Modules\Projects;

use App\Models\User;
use App\Modules\Projects\Enums\ProjectStatusCategory;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\ProjectStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Modules\Core\InteractsWithModules;
use Tests\TestCase;

class ProjectStatusSettingsTest extends TestCase
{
    use InteractsWithModules, RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
        $this->admin = User::factory()->superAdmin()->create();
    }

    public function test_the_five_statuses_of_the_prototype_exist_after_migrating(): void
    {
        $this->assertSame(
            ['Envisagé', 'En cours', 'En pause', 'Terminé', 'Abandonné'],
            ProjectStatus::query()->ordered()->pluck('name')->all(),
        );
    }

    public function test_the_page_lists_statuses_with_their_usage(): void
    {
        Project::factory()->count(2)->withStatus(ProjectStatusCategory::Active)->create();

        $statuses = $this->actingAs($this->admin)->get(route('settings.project-statuses.index'))->assertOk()->viewData('page')['props']['statuses'];

        $active = collect($statuses)->firstWhere('category', 'active');
        $this->assertSame(2, $active['usage_count']);
    }

    public function test_a_status_can_be_added_renamed_and_moved(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('settings.project-statuses.store'), ['name' => 'En recette', 'tone' => 'accent', 'category' => 'active'])->assertSessionHasNoErrors();
        $added = ProjectStatus::query()->where('name', 'En recette')->firstOrFail();
        $this->assertSame(5, $added->position);

        $this->patch(route('settings.project-statuses.update', $added->id), ['name' => 'Recette', 'tone' => 'info', 'category' => 'active'])->assertSessionHasNoErrors();
        $this->assertSame('Recette', $added->fresh()?->name);

        $this->patch(route('settings.project-statuses.move', $added->id), ['direction' => 'up']);
        $this->assertSame(
            ['Envisagé', 'En cours', 'En pause', 'Terminé', 'Recette', 'Abandonné'],
            ProjectStatus::query()->ordered()->pluck('name')->all(),
        );
    }

    public function test_deleting_a_used_status_requires_a_replacement_and_moves_the_projects(): void
    {
        $paused = ProjectStatus::firstOf(ProjectStatusCategory::Paused);
        $active = ProjectStatus::firstOf(ProjectStatusCategory::Active);
        $this->assertNotNull($paused);
        $this->assertNotNull($active);
        $project = Project::factory()->withStatus(ProjectStatusCategory::Paused)->create();

        $this->actingAs($this->admin);

        $this->delete(route('settings.project-statuses.destroy', $paused->id))->assertSessionHasErrors('replacement_id');
        $this->assertModelExists($paused);

        $this->delete(route('settings.project-statuses.destroy', $paused->id), ['replacement_id' => $active->id])->assertSessionHasNoErrors();
        $this->assertModelMissing($paused);
        $this->assertSame($active->id, $project->fresh()?->status_id);
        $this->assertSame(range(0, 3), ProjectStatus::query()->ordered()->pluck('position')->all());
    }

    public function test_the_last_status_cannot_be_deleted(): void
    {
        $last = ProjectStatus::query()->ordered()->firstOrFail();
        ProjectStatus::query()->whereKeyNot($last->id)->delete();

        $this->actingAs($this->admin)->delete(route('settings.project-statuses.destroy', $last->id))->assertSessionHasErrors('status');
        $this->assertModelExists($last);
    }

    public function test_an_unknown_behaviour_or_colour_is_refused(): void
    {
        $this->actingAs($this->admin)
            ->post(route('settings.project-statuses.store'), ['name' => 'X', 'tone' => 'pink', 'category' => 'whatever'])
            ->assertSessionHasErrors(['tone', 'category']);
    }

    public function test_only_the_super_admin_manages_statuses(): void
    {
        $administrator = $this->userWithRole('Administrateur');
        $status = ProjectStatus::query()->firstOrFail();

        $this->actingAs($administrator)->get(route('settings.project-statuses.index'))->assertForbidden();
        $this->actingAs($administrator)->delete(route('settings.project-statuses.destroy', $status->id))->assertForbidden();
    }
}
