<?php

namespace App\Modules\Core;

use RuntimeException;

class ModuleStateException extends RuntimeException
{
    public static function required(Module $module): self
    {
        return new self("Le module « {$module->name()} » est obligatoire et ne peut pas être désactivé.");
    }

    /**
     * @param  list<Module>  $dependents
     */
    public static function hasDependents(Module $module, array $dependents): self
    {
        $names = implode(', ', array_map(fn (Module $dependent): string => "« {$dependent->name()} »", $dependents));

        return new self("Le module « {$module->name()} » ne peut pas être désactivé : {$names} en dépend.");
    }

    /**
     * @param  list<Module>  $missing
     */
    public static function missingDependencies(Module $module, array $missing): self
    {
        $names = implode(', ', array_map(fn (Module $dependency): string => "« {$dependency->name()} »", $missing));

        return new self("Activez d’abord {$names} : le module « {$module->name()} » en dépend.");
    }
}
