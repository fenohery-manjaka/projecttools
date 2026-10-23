<?php

namespace App\Modules\Core\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Modules\Core\Http\Requests\Settings\UpdateModuleRequest;
use App\Modules\Core\Module;
use App\Modules\Core\ModuleRegistry;
use App\Modules\Core\ModuleStateException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ModuleController extends Controller
{
    public function index(ModuleRegistry $modules): Response
    {
        $all = $modules->all();

        return Inertia::render('settings/Modules', [
            'modules' => array_values(array_map(fn (Module $module): array => [
                'key' => $module->key(),
                'name' => $module->name(),
                'description' => $module->description(),
                'required' => $module->isRequired(),
                'enabled' => $modules->isEnabled($module->key()),
                'dependencies' => array_map(fn (Module $dependency): string => $dependency->name(), $modules->dependenciesOf($module->key())),
                'dependents' => array_values(array_map(
                    fn (Module $dependent): string => $dependent->name(),
                    array_filter($all, fn (Module $other): bool => in_array($module->key(), $other->dependencies(), true)),
                )),
            ], $all)),
        ]);
    }

    public function update(UpdateModuleRequest $request, ModuleRegistry $modules, string $module): RedirectResponse
    {
        abort_unless($modules->has($module), 404);

        try {
            $request->boolean('enabled') ? $modules->enable($module) : $modules->disable($module);
        } catch (ModuleStateException $exception) {
            throw ValidationException::withMessages(['enabled' => $exception->getMessage()]);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => sprintf('Module « %s » %s.', $modules->get($module)->name(), $request->boolean('enabled') ? 'activé' : 'désactivé'),
        ]);

        return to_route('settings.modules.index');
    }
}
