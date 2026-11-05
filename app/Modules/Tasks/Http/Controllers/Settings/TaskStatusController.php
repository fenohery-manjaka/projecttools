<?php

namespace App\Modules\Tasks\Http\Controllers\Settings;

use App\Modules\Core\Http\Controllers\Settings\StatusSettingsController;
use App\Modules\Tasks\Models\TaskStatus;

/**
 * Settings › Task statuses (also the columns of the Kanban board).
 */
class TaskStatusController extends StatusSettingsController
{
    protected function model(): string
    {
        return TaskStatus::class;
    }

    protected function page(): string
    {
        return 'settings/TaskStatuses';
    }

    protected function indexRoute(): string
    {
        return 'settings.task-statuses.index';
    }
}
