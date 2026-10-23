<?php

namespace App\Modules\Core\Http\Middleware;

use App\Modules\Core\ModuleRegistry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Answers 404 on the routes of a disabled module, even for the super admin.
 */
class EnsureModuleEnabled
{
    public function __construct(private readonly ModuleRegistry $modules) {}

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        abort_unless($this->modules->has($module) && $this->modules->isEnabled($module), 404);

        return $next($request);
    }
}
