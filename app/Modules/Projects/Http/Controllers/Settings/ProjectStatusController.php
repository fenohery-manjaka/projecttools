<?php

namespace App\Modules\Projects\Http\Controllers\Settings;

use App\Modules\Core\Http\Controllers\Settings\StatusSettingsController;
use App\Modules\Projects\Models\ProjectStatus;

/**
 * Settings › Project statuses.
 */
class ProjectStatusController extends StatusSettingsController
{
    protected function model(): string
    {
        return ProjectStatus::class;
    }

    protected function page(): string
    {
        return 'settings/ProjectStatuses';
    }

    protected function indexRoute(): string
    {
        return 'settings.project-statuses.index';
    }
}
