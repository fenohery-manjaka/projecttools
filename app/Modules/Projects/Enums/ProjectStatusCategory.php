<?php

namespace App\Modules\Projects\Enums;

use App\Modules\Core\Statuses\StatusCategory;

/**
 * Behaviour behind a project status; the statuses themselves are configurable (ProjectStatus model).
 */
enum ProjectStatusCategory: string implements StatusCategory
{
    case Planned = 'planned';
    case Active = 'active';
    case Paused = 'paused';
    case Done = 'done';
    case Abandoned = 'abandoned';

    public function label(): string
    {
        return match ($this) {
            self::Planned => 'Envisagé',
            self::Active => 'En cours',
            self::Paused => 'En pause',
            self::Done => 'Terminé',
            self::Abandoned => 'Abandonné',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Planned => 'Projet pas encore commencé ; statut des nouveaux projets.',
            self::Active => 'Compté dans « Projets en cours » sur l’accueil.',
            self::Paused => 'Projet arrêté pour un temps.',
            self::Done => 'Projet fini : sa progression vaut 100 % même sans tâche.',
            self::Abandoned => 'Projet arrêté définitivement.',
        };
    }
}
