<?php

namespace App\Modules\Core\Statuses;

/**
 * The fixed behaviour behind a configurable status: what the application does with it,
 * whatever name the team gives it. Implemented by a backed enum of each module.
 */
interface StatusCategory
{
    public function label(): string;

    /**
     * What choosing this behaviour does, shown in the settings.
     */
    public function description(): string;
}
