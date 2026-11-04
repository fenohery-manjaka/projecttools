<?php

namespace App\Modules\Tasks;

use App\Modules\Core\Module;

class TasksModule extends Module
{
    public function key(): string
    {
        return 'tasks';
    }

    public function name(): string
    {
        return 'Tâches';
    }

    public function description(): string
    {
        return 'Tâches, responsables, échéances et sous-tâches.';
    }

    public function isRequired(): bool
    {
        return true;
    }

    public function dependencies(): array
    {
        return ['projects'];
    }

    public function permissions(): array
    {
        return [
            'tasks.view' => 'Voir les tâches des projets visibles',
            'tasks.create' => 'Créer une tâche',
            'tasks.update' => 'Modifier une tâche',
            'tasks.delete' => 'Supprimer une tâche',
            'tasks.comment' => 'Commenter une tâche',
        ];
    }
}
