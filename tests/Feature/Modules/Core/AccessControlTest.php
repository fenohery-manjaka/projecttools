<?php

namespace Tests\Feature\Modules\Core;

use App\Models\User;
use App\Modules\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The four checks every module route goes through: module enabled, super admin, role permission, project membership.
 */
class AccessControlTest extends TestCase
{
    use InteractsWithModules, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
    }

    public function test_the_super_admin_passes_every_permission_check_without_any_role(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $project = Project::factory()->create();

        $this->actingAs($admin);

        $this->get(route('projects.index'))->assertOk();
        $this->get(route('projects.show', $project))->assertOk();
        $this->get(route('projects.create'))->assertOk();
        $this->get(route('settings.roles.index'))->assertOk();
        $this->get(route('settings.modules.index'))->assertOk();
        $this->get(route('settings.members.index'))->assertOk();
    }

    public function test_a_user_without_the_permission_is_refused(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('projects.index'))->assertForbidden();
        $this->get(route('settings.members.index'))->assertForbidden();
    }

    public function test_a_role_permission_opens_only_what_it_names(): void
    {
        $this->actingAs($this->userWithPermissions('projects.view'));

        $this->get(route('projects.index'))->assertOk();
        $this->get(route('projects.create'))->assertForbidden();
        $this->post(route('projects.store'), [])->assertForbidden();
    }

    public function test_settings_for_roles_and_modules_are_reserved_to_the_super_admin(): void
    {
        $this->actingAs($this->userWithRole('Administrateur'));

        $this->get(route('settings.roles.index'))->assertForbidden();
        $this->get(route('settings.modules.index'))->assertForbidden();
        $this->get(route('settings.members.index'))->assertOk();
    }

    public function test_project_pages_require_membership_unless_the_user_may_view_all_projects(): void
    {
        $project = Project::factory()->create();

        $this->actingAs($this->userWithPermissions('projects.view'));
        $this->get(route('projects.show', $project))->assertForbidden();

        $member = $this->userWithPermissions('projects.view');
        $project->members()->attach($member);
        $this->actingAs($member);
        $this->get(route('projects.show', $project))->assertOk();

        $this->actingAs($this->userWithPermissions('projects.view-all'));
        $this->get(route('projects.show', $project))->assertOk();
    }

    public function test_the_sidebar_only_lists_what_the_user_may_open(): void
    {
        $this->actingAs(User::factory()->create());
        $this->assertSame(['Accueil'], $this->navigationTitles($this->get(route('dashboard'))));

        $this->actingAs($this->userWithPermissions('projects.view'));
        $this->assertSame(['Accueil', 'Projets'], $this->navigationTitles($this->get(route('dashboard'))));
    }

    public function test_a_deactivated_member_is_signed_out(): void
    {
        $this->actingAs(User::factory()->deactivated()->create());

        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
