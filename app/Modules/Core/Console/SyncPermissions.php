<?php

namespace App\Modules\Core\Console;

use App\Modules\Core\ModuleRegistry;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

#[Signature('projecttools:sync-permissions')]
#[Description('Write the permissions declared by the modules into the database and remove the obsolete ones')]
class SyncPermissions extends Command
{
    public function handle(ModuleRegistry $modules, PermissionRegistrar $registrar): int
    {
        $declared = array_keys($modules->permissions());

        foreach ($declared as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $removed = Permission::query()->whereNotIn('name', $declared)->delete();

        $registrar->forgetCachedPermissions();

        $this->components->info(sprintf('%d permission(s) declared, %d obsolete removed.', count($declared), $removed));

        return self::SUCCESS;
    }
}
