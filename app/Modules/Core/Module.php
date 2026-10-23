<?php

namespace App\Modules\Core;

use App\Modules\Core\Navigation\NavigationItem;
use ReflectionClass;

/**
 * A functional block of the application (Projects, Tasks, Ideas…).
 *
 * Each module lives in app/Modules/<Name>, declares itself by extending this
 * class, and is listed in config/modules.php. See docs/architecture.md.
 */
abstract class Module
{
    /**
     * Technical key used in routes (module:<key>), the modules table and permission names.
     */
    abstract public function key(): string;

    /**
     * Name shown to users.
     */
    abstract public function name(): string;

    public function description(): string
    {
        return '';
    }

    /**
     * A required module can never be disabled from the settings.
     */
    public function isRequired(): bool
    {
        return false;
    }

    /**
     * Keys of the modules this one cannot work without.
     *
     * @return list<string>
     */
    public function dependencies(): array
    {
        return [];
    }

    /**
     * Permissions this module checks, as name => label shown in the roles matrix.
     *
     * @return array<string, string>
     */
    public function permissions(): array
    {
        return [];
    }

    /**
     * Entries this module adds to the sidebar.
     *
     * @return list<NavigationItem>
     */
    public function navigation(): array
    {
        return [];
    }

    /**
     * Module-specific bootstrapping (observers, extra gates…), run once its routes are loaded.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Absolute path inside the module directory.
     */
    public function path(string $path = ''): string
    {
        $directory = dirname((string) (new ReflectionClass($this))->getFileName());

        return $path === '' ? $directory : $directory.DIRECTORY_SEPARATOR.$path;
    }
}
