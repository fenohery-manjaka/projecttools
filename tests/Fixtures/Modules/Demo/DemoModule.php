<?php

namespace Tests\Fixtures\Modules\Demo;

use App\Modules\Core\Module;
use App\Modules\Core\Navigation\NavigationGroup;
use App\Modules\Core\Navigation\NavigationItem;

/**
 * An optional module used only by the tests: the real optional modules arrive in lots 1 to 3.
 */
class DemoModule extends Module
{
    public function key(): string
    {
        return 'demo';
    }

    public function name(): string
    {
        return 'Démo';
    }

    public function dependencies(): array
    {
        return ['projects'];
    }

    public function permissions(): array
    {
        return ['demo.view' => 'Voir la démo'];
    }

    public function navigation(): array
    {
        return [
            new NavigationItem(title: 'Démo', route: 'demo.index', icon: 'Circle', group: NavigationGroup::Collaboration, ability: 'demo.view'),
        ];
    }
}
