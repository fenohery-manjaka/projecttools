<?php

namespace App\Modules\Core\Statuses;

/**
 * Pill colours a status may take. The value is the variant of the Vue Badge component.
 */
enum StatusTone: string
{
    case Neutral = 'secondary';
    case Blue = 'info';
    case Orange = 'warn';
    case Green = 'ok';
    case Red = 'bad';
    case Brand = 'accent';

    public function label(): string
    {
        return match ($this) {
            self::Neutral => 'Gris',
            self::Blue => 'Bleu',
            self::Orange => 'Orange',
            self::Green => 'Vert',
            self::Red => 'Rouge',
            self::Brand => 'Couleur de la marque',
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $tone): array => ['value' => $tone->value, 'label' => $tone->label()],
            self::cases(),
        );
    }
}
