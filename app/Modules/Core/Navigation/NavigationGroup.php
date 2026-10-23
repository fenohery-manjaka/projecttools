<?php

namespace App\Modules\Core\Navigation;

enum NavigationGroup: string
{
    case Work = 'work';
    case Collaboration = 'collaboration';
    case Steering = 'steering';

    public function label(): string
    {
        return match ($this) {
            self::Work => 'Travail',
            self::Collaboration => 'Collaboration',
            self::Steering => 'Pilotage',
        };
    }
}
