<?php

namespace App\Modules\Core;

use Illuminate\Database\Migrations\Migrator;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;

/**
 * Wires a module into the application: migrations, routes, then its own boot().
 */
class ModuleLoader
{
    public function __construct(private readonly Application $app) {}

    public function load(Module $module): void
    {
        if (is_dir($migrations = $module->path('Database/Migrations'))) {
            $this->registerMigrations($migrations);
        }

        if (is_file($routes = $module->path('routes.php')) && ! $this->app->routesAreCached()) {
            Route::middleware(['web', 'auth', 'verified', 'module:'.$module->key()])->group($routes);
        }

        $module->boot();
    }

    private function registerMigrations(string $path): void
    {
        $register = fn (Migrator $migrator) => $migrator->path($path);

        $this->app->afterResolving('migrator', $register);

        if ($this->app->resolved('migrator')) {
            $register($this->app->make('migrator'));
        }
    }
}
