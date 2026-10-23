<?php

namespace App\Modules\Core\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Modules\Core\Http\Requests\Settings\RoleRequest;
use App\Modules\Core\Module;
use App\Modules\Core\ModuleRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(ModuleRegistry $modules): Response
    {
        $roles = Role::query()
            ->with('permissions:id,name')
            ->withCount('users')
            ->orderBy('id')
            ->get();

        return Inertia::render('settings/Roles', [
            'roles' => $roles->map(fn (Role $role): array => [
                'id' => $role->id,
                'name' => $role->name,
                'users_count' => $role->users_count,
                'permissions' => $role->permissions->pluck('name'),
            ]),
            'groups' => array_values(array_map(fn (Module $module): array => [
                'key' => $module->key(),
                'name' => $module->name(),
                'enabled' => $modules->isEnabled($module->key()),
                'permissions' => array_map(
                    fn (string $name, string $label): array => ['name' => $name, 'label' => $label],
                    array_keys($module->permissions()),
                    $module->permissions(),
                ),
            ], array_filter($modules->all(), fn (Module $module): bool => $module->permissions() !== []))),
        ]);
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        Role::create(['name' => $request->string('name')->trim()->value(), 'guard_name' => 'web']);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Rôle créé.']);

        return to_route('settings.roles.index');
    }

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        $role->update(['name' => $request->string('name')->trim()->value()]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Rôle renommé.']);

        return to_route('settings.roles.index');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->users()->exists()) {
            throw ValidationException::withMessages(['role' => "Le rôle « {$role->name} » est encore attribué à des membres."]);
        }

        $role->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Rôle supprimé.']);

        return to_route('settings.roles.index');
    }
}
