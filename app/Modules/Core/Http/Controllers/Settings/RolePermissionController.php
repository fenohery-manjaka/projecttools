<?php

namespace App\Modules\Core\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Modules\Core\Http\Requests\Settings\UpdateRolePermissionRequest;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * One checkbox of the roles × permissions matrix.
 */
class RolePermissionController extends Controller
{
    public function update(UpdateRolePermissionRequest $request, Role $role): RedirectResponse
    {
        $permission = Permission::findByName($request->string('permission')->value(), 'web');

        $request->boolean('granted')
            ? $role->givePermissionTo($permission)
            : $role->revokePermissionTo($permission);

        return back();
    }
}
