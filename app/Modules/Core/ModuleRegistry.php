<?php

namespace App\Modules\Core;

use App\Models\User;
use App\Modules\Core\Models\ModuleState;
use App\Modules\Core\Navigation\NavigationItem;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

/**
 * Knows every module, whether it is enabled, and what it declares.
 */
class ModuleRegistry
{
    /** @var array<string, Module> */
    private array $modules = [];

    /** @var array<string, bool>|null */
    private ?array $states = null;

    /**
     * @param  iterable<Module>  $modules
     */
    public function __construct(iterable $modules = [])
    {
        foreach ($modules as $module) {
            $this->register($module);
        }
    }

    public function register(Module $module): void
    {
        if (isset($this->modules[$module->key()])) {
            throw new InvalidArgumentException("Module [{$module->key()}] is already registered.");
        }

        $this->modules[$module->key()] = $module;
    }

    /**
     * @return array<string, Module>
     */
    public function all(): array
    {
        return $this->modules;
    }

    public function has(string $key): bool
    {
        return isset($this->modules[$key]);
    }

    public function get(string $key): Module
    {
        return $this->modules[$key] ?? throw new InvalidArgumentException("Module [{$key}] is not registered.");
    }

    public function isEnabled(string $key): bool
    {
        $module = $this->get($key);

        return $module->isRequired() || ($this->states()[$key] ?? true);
    }

    /**
     * @return array<string, Module>
     */
    public function enabled(): array
    {
        return array_filter($this->modules, fn (Module $module): bool => $this->isEnabled($module->key()));
    }

    /**
     * Enabled modules that would break if the given module were disabled.
     *
     * @return list<Module>
     */
    public function enabledDependents(string $key): array
    {
        return array_values(array_filter(
            $this->enabled(),
            fn (Module $module): bool => in_array($key, $module->dependencies(), true),
        ));
    }

    /**
     * @return list<Module>
     */
    public function dependenciesOf(string $key): array
    {
        return array_map($this->get(...), $this->get($key)->dependencies());
    }

    /**
     * @throws ModuleStateException when a dependency is disabled
     */
    public function enable(string $key): void
    {
        $module = $this->get($key);

        $missing = array_values(array_filter(
            $this->dependenciesOf($key),
            fn (Module $dependency): bool => ! $this->isEnabled($dependency->key()),
        ));

        if ($missing !== []) {
            throw ModuleStateException::missingDependencies($module, $missing);
        }

        $this->persist($key, true);
    }

    /**
     * @throws ModuleStateException when the module is required or another enabled module needs it
     */
    public function disable(string $key): void
    {
        $module = $this->get($key);

        if ($module->isRequired()) {
            throw ModuleStateException::required($module);
        }

        $dependents = $this->enabledDependents($key);

        if ($dependents !== []) {
            throw ModuleStateException::hasDependents($module, $dependents);
        }

        $this->persist($key, false);
    }

    /**
     * Every permission declared by every module, enabled or not.
     *
     * @return array<string, string>
     */
    public function permissions(): array
    {
        return array_merge(...array_values(array_map(
            fn (Module $module): array => $module->permissions(),
            $this->modules,
        )));
    }

    /**
     * Sidebar entries the user may see, from enabled modules only.
     *
     * @return list<NavigationItem>
     */
    public function navigationFor(User $user): array
    {
        $items = [];

        foreach ($this->enabled() as $module) {
            foreach ($module->navigation() as $item) {
                if ($item->ability === null || Gate::forUser($user)->allows($item->ability, $item->abilityArguments)) {
                    $items[] = $item;
                }
            }
        }

        usort($items, fn (NavigationItem $a, NavigationItem $b): int => $a->order <=> $b->order);

        return $items;
    }

    public function flushStates(): void
    {
        $this->states = null;
    }

    /**
     * @return array<string, bool>
     */
    private function states(): array
    {
        return $this->states ??= ModuleState::query()
            ->pluck('enabled', 'key')
            ->map(fn (mixed $enabled): bool => (bool) $enabled)
            ->all();
    }

    private function persist(string $key, bool $enabled): void
    {
        ModuleState::query()->updateOrCreate(['key' => $key], ['enabled' => $enabled]);

        $this->flushStates();
    }
}
