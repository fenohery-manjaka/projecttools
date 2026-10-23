<?php

namespace App\Modules\Projects;

use App\Modules\Core\Module;
use App\Modules\Core\Navigation\NavigationGroup;
use App\Modules\Core\Navigation\NavigationItem;
use App\Modules\Projects\Models\Project;

/**
 * Reference module: copy its structure when building a new one (docs/architecture.md).
 */
class ProjectsModule extends Module
{
    public function key(): string
    {
        return 'projects';
    }

    public function name(): string
    {
        return 'Projets';
    }

    public function description(): string
    {
        return 'Projets, responsables et membres.';
    }

    public function isRequired(): bool
    {
        return true;
    }

    public function permissions(): array
    {
        return [
            'projects.view' => 'Voir les projets dont on est membre',
            'projects.view-all' => 'Voir tous les projets',
            'projects.create' => 'Créer un projet',
            'projects.update' => 'Modifier un projet',
            'projects.delete' => 'Supprimer un projet',
            'projects.manage-members' => 'Gérer les membres d’un projet',
        ];
    }

    public function navigation(): array
    {
        return [
            new NavigationItem(
                title: 'Projets',
                route: 'projects.index',
                icon: 'Folder',
                group: NavigationGroup::Work,
                ability: 'viewAny',
                abilityArguments: [Project::class],
                order: 20,
            ),
        ];
    }
}
