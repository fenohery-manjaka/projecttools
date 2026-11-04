<?php

namespace App\Modules\Tasks\Enums;

enum TaskPriority: string
{
    case Urgent = 'urgent';
    case High = 'high';
    case Normal = 'normal';
    case Low = 'low';

    public function label(): string
    {
        return match ($this) {
            self::Urgent => 'Urgente',
            self::High => 'Haute',
            self::Normal => 'Normale',
            self::Low => 'Basse',
        };
    }

    /**
     * Sort key: 0 is the most urgent.
     */
    public function rank(): int
    {
        return match ($this) {
            self::Urgent => 0,
            self::High => 1,
            self::Normal => 2,
            self::Low => 3,
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $priority): array => ['value' => $priority->value, 'label' => $priority->label()],
            self::cases(),
        );
    }
}
