<?php

namespace App\Modules\Core;

use App\Modules\Core\Navigation\NavigationGroup;
use App\Modules\Core\Navigation\NavigationItem;

/**
 * Members, roles, settings and the module registry itself. Always enabled.
 */
class CoreModule extends Module
{
    public function key(): string
    {
        return 'core';
    }

    public function name(): string
    {
        return 'Noyau';
    }

    public function description(): string
    {
        return 'Membres, rôles et permissions, paramètres.';
    }

    public function isRequired(): bool
    {
        return true;
    }

    public function permissions(): array
    {
        return [
            'members.view' => 'Voir la liste des membres',
            'members.manage' => 'Inviter des membres, changer leur rôle, les désactiver',
        ];
    }

    public function navigation(): array
    {
        return [
            new NavigationItem(title: 'Accueil', route: 'dashboard', icon: 'LayoutGrid', group: NavigationGroup::Work, order: 10),
        ];
    }
}
