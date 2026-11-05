<?php

namespace App\Modules\Tasks\Enums;

use App\Modules\Core\Statuses\StatusCategory;

/**
 * Behaviour behind a task status; the statuses themselves are configurable (TaskStatus model).
 */
enum TaskStatusCategory: string implements StatusCategory
{
    case Todo = 'todo';
    case Doing = 'doing';
    case Blocked = 'blocked';
    case Done = 'done';

    public function label(): string
    {
        return match ($this) {
            self::Todo => 'À faire',
            self::Doing => 'En cours',
            self::Blocked => 'Bloqué',
            self::Done => 'Terminé',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Todo => 'Tâche ouverte, pas commencée ; statut des nouvelles tâches et des tâches rouvertes.',
            self::Doing => 'Tâche ouverte, en cours.',
            self::Blocked => 'Tâche ouverte qui demande la raison du blocage, et apparaît dans « Demande une intervention ».',
            self::Done => 'Tâche fermée : sa date de fin est remplie, elle n’est plus en retard.',
        };
    }

    public function isOpen(): bool
    {
        return $this !== self::Done;
    }
}
