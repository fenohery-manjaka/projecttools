<?php

namespace Tests\Feature\Architecture;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as Router;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Guard rail for the executor: a module route without an access check fails the CI.
 */
class ModuleRoutesAreProtectedTest extends TestCase
{
    public function test_every_module_route_declares_an_access_check(): void
    {
        $moduleRoutes = collect(Router::getRoutes()->getRoutes())
            ->filter(fn (Route $route): bool => $this->middlewareStartingWith($route, 'module:') !== []);

        $this->assertNotEmpty($moduleRoutes, 'No module route found: is the ModuleLoader still running?');

        $unprotected = $moduleRoutes
            ->filter(fn (Route $route): bool => $this->middlewareStartingWith($route, 'can:') === [])
            ->map(fn (Route $route): string => implode('|', $route->methods()).' '.$route->uri())
            ->values()
            ->all();

        $this->assertSame([], $unprotected, 'These module routes have no ->can() check: '.implode(', ', $unprotected));
    }

    public function test_every_module_route_requires_authentication(): void
    {
        $missing = collect(Router::getRoutes()->getRoutes())
            ->filter(fn (Route $route): bool => $this->middlewareStartingWith($route, 'module:') !== [])
            ->reject(fn (Route $route): bool => in_array('auth', $route->gatherMiddleware(), true))
            ->map(fn (Route $route): string => $route->uri())
            ->values()
            ->all();

        $this->assertSame([], $missing);
    }

    /**
     * @return list<string>
     */
    private function middlewareStartingWith(Route $route, string $prefix): array
    {
        return array_values(array_filter(
            $route->gatherMiddleware(),
            fn (mixed $middleware): bool => is_string($middleware) && Str::startsWith($middleware, $prefix),
        ));
    }
}
