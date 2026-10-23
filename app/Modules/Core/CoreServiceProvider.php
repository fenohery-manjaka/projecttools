<?php

namespace App\Modules\Core;

use App\Models\User;
use App\Modules\Core\Console\MakeSuperAdmin;
use App\Modules\Core\Console\SyncPermissions;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModuleRegistry::class, function (Application $app): ModuleRegistry {
            /** @var list<class-string<Module>> $classes */
            $classes = config('modules.modules', []);

            return new ModuleRegistry(array_map(fn (string $class): Module => $app->make($class), $classes));
        });
    }

    public function boot(ModuleRegistry $modules, ModuleLoader $loader): void
    {
        $this->configureGates();

        foreach ($modules->all() as $module) {
            $loader->load($module);
        }

        if ($this->app->runningInConsole()) {
            $this->commands([SyncPermissions::class, MakeSuperAdmin::class]);
        }
    }

    private function configureGates(): void
    {
        // The super admin passes every check. Disabled modules still answer 404 (EnsureModuleEnabled runs first).
        Gate::before(fn (User $user): ?bool => $user->is_super_admin ? true : null);

        // Settings › Roles and Settings › Modules: nobody but the super admin.
        Gate::define('administer', fn (User $user): bool => false);
    }
}
