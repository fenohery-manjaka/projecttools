<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Initial roles, taken from the prototype. The super admin edits them afterwards
 * in Settings › Roles, so this seeder only fills roles that do not exist yet.
 *
 * A new module adds its permissions to the lists below.
 */
class RoleSeeder extends Seeder
{
    /**
     * @var array<string, list<string>|'*'>
     */
    private const array ROLES = [
        'Administrateur' => '*',
        'Responsable' => [
            'members.view',
            'projects.view', 'projects.view-all', 'projects.create', 'projects.update', 'projects.manage-members',
        ],
        'Contributeur' => [
            'members.view',
            'projects.view',
        ],
        'Observateur' => [
            'members.view',
            'projects.view',
        ],
    ];

    public function run(): void
    {
        Artisan::call('projecttools:sync-permissions');

        foreach (self::ROLES as $name => $permissions) {
            $role = Role::findOrCreate($name, 'web');

            if ($role->wasRecentlyCreated) {
                $role->syncPermissions($permissions === '*' ? Permission::all() : $permissions);
            }
        }
    }
}
