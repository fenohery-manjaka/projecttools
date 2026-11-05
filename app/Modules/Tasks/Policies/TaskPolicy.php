<?php

namespace App\Modules\Tasks\Policies;

use App\Models\User;
use App\Modules\Projects\Models\Project;
use App\Modules\Tasks\Models\Task;

/**
 * Check 3 (role permission) then check 4 (the task's project is visible). The super admin never reaches this class.
 */
class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('tasks.view');
    }

    public function view(User $user, Task $task): bool
    {
        return $user->checkPermissionTo('tasks.view') && $user->can('view', $task->project);
    }

    public function create(User $user, Project $project): bool
    {
        return $user->checkPermissionTo('tasks.create') && $user->can('view', $project);
    }

    public function update(User $user, Task $task): bool
    {
        return $user->checkPermissionTo('tasks.update') && $user->can('view', $task->project);
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->checkPermissionTo('tasks.delete') && $user->can('view', $task->project);
    }

    public function comment(User $user, Task $task): bool
    {
        return $user->checkPermissionTo('tasks.comment') && $user->can('view', $task->project);
    }
}
