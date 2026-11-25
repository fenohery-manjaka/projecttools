<?php

namespace App\Modules\Core\Agenda;

final readonly class AgendaItem
{
    public function __construct(
        public string $date,
        public ?string $time,
        public AgendaItemType $type,
        public string $label,
        public string $url,
        public string $color,
        public bool $isLate,
        public bool $isDone,
        public string $projectName,
    ) {}
}
