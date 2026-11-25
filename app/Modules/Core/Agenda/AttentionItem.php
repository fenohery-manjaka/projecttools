<?php

namespace App\Modules\Core\Agenda;

final readonly class AttentionItem
{
    /**
     * @param  list<string>  $assignees
     */
    public function __construct(
        public Severity $severity,
        public string $label,
        public string $detail,
        public string $url,
        public string $projectName,
        public string $kind,
        public ?int $daysLate = null,
        public array $assignees = [],
        public ?string $date = null,
    ) {}
}
