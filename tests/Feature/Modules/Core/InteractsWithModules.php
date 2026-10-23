<?php

namespace Tests\Feature\Modules\Core;

use App\Models\User;
use App\Modules\Core\Module;
use App\Modules\Core\ModuleLoader;
use App\Modules\Core\ModuleRegistry;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\Models\Role;

trait InteractsWithModules
{
    protected function seedRoles(): void
    {
        $this->seed(RoleSeeder::class);
    }

    /**
     * Register a module after boot, the way the CoreServiceProvider does for config/modules.php.
     */
    protected function registerModule(Module $module): void
    {
        $this->app->make(ModuleRegistry::class)->register($module);
        $this->app->make(ModuleLoader::class)->load($module);

        Route::getRoutes()->refreshNameLookups();
        Artisan::call('projecttools:sync-permissions');
    }

    protected function userWithRole(string $role): User
    {
        return User::factory()->create()->assignRole($role);
    }

    /**
     * A user whose role holds exactly the given permissions.
     */
    protected function userWithPermissions(string ...$permissions): User
    {
        $role = Role::findOrCreate('Test '.uniqid(), 'web')->syncPermissions($permissions);

        return User::factory()->create()->assignRole($role);
    }

    /**
     * Titles of the sidebar entries shared with the page.
     *
     * @return list<string>
     */
    protected function navigationTitles(TestResponse $response): array
    {
        /** @var list<array{items: list<array{title: string}>}> $groups */
        $groups = $response->viewData('page')['props']['navigation'];

        return array_merge(...array_map(fn (array $group): array => array_column($group['items'], 'title'), $groups));
    }
}
