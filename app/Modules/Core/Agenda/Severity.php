<?php

namespace App\Modules\Core\Agenda;

enum Severity: string
{
    case Critical = 'critical';
    case Warning = 'warning';
    case Info = 'info';

    public function order(): int
    {
        return match ($this) {
            self::Critical => 0,
            self::Warning => 1,
            self::Info => 2,
        };
    }
}
