<?php

namespace Tests\Feature\Modules\Core;

use App\Models\User;
use App\Modules\Core\ModuleRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolesTest extends TestCase
{
    use InteractsWithModules, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
    }

    public function test_unchecking_a_permission_in_the_matrix_removes_the_access(): void
    {
        $contributor = $this->userWithRole('Contributeur');
        $this->actingAs($contributor)->get(route('projects.index'))->assertOk();

        $contributeur = Role::findByName('Contributeur', 'web');

        $this->actingAs(User::factory()->superAdmin()->create())
            ->put(route('settings.roles.permissions.update', $contributeur), ['permission' => 'projects.view', 'granted' => false])
            ->assertSessionHasNoErrors();

        $this->actingAs($contributor->fresh())->get(route('projects.index'))->assertForbidden();

        $this->actingAs(User::factory()->superAdmin()->create())
            ->put(route('settings.roles.permissions.update', $contributeur), ['permission' => 'projects.view', 'granted' => true]);

        $this->actingAs($contributor->fresh())->get(route('projects.index'))->assertOk();
    }

    public function test_only_declared_permissions_can_be_granted(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create())
            ->put(route('settings.roles.permissions.update', Role::findByName('Observateur', 'web')), ['permission' => 'everything', 'granted' => true])
            ->assertSessionHasErrors('permission');
    }

    public function test_roles_can_be_created_renamed_and_deleted_when_unused(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        $this->post(route('settings.roles.store'), ['name' => 'Stagiaire'])->assertSessionHasNoErrors();
        $role = Role::findByName('Stagiaire', 'web');

        $this->patch(route('settings.roles.update', $role), ['name' => 'Alternant'])->assertSessionHasNoErrors();
        $this->assertSame('Alternant', $role->fresh()?->name);

        $this->post(route('settings.roles.store'), ['name' => 'Alternant'])->assertSessionHasErrors('name');

        User::factory()->create()->assignRole('Alternant');
        $this->delete(route('settings.roles.destroy', $role))->assertSessionHasErrors('role');

        $role->users()->detach();
        $this->delete(route('settings.roles.destroy', $role))->assertSessionHasNoErrors();
        $this->assertNull(Role::query()->where('name', 'Alternant')->first());
    }

    public function test_the_matrix_lists_every_module_permission(): void
    {
        $response = $this->actingAs(User::factory()->superAdmin()->create())->get(route('settings.roles.index'));

        /** @var list<array{permissions: list<array{name: string}>}> $groups */
        $groups = $response->viewData('page')['props']['groups'];
        $listed = array_merge(...array_map(fn (array $group): array => array_column($group['permissions'], 'name'), $groups));

        $this->assertEqualsCanonicalizing(array_keys($this->app->make(ModuleRegistry::class)->permissions()), $listed);
    }

    public function test_sync_command_writes_declared_permissions_and_removes_obsolete_ones(): void
    {
        Permission::findOrCreate('legacy.permission', 'web');

        Artisan::call('projecttools:sync-permissions');

        $this->assertEqualsCanonicalizing(
            array_keys($this->app->make(ModuleRegistry::class)->permissions()),
            Permission::query()->pluck('name')->all(),
        );
    }

    public function test_the_super_admin_flag_is_set_by_command_only(): void
    {
        $user = User::factory()->create(['email' => 'boss@example.com']);

        $this->artisan('projecttools:super-admin', ['email' => 'boss@example.com'])->assertSuccessful();
        $this->assertTrue($user->fresh()?->is_super_admin);

        $this->artisan('projecttools:super-admin', ['email' => 'boss@example.com', '--revoke' => true])->assertSuccessful();
        $this->assertFalse($user->fresh()?->is_super_admin);

        $this->artisan('projecttools:super-admin', ['email' => 'nobody@example.com'])->assertFailed();
    }
}
