<?php

namespace App\Modules\Projects\Policies;

use App\Models\User;
use App\Modules\Projects\Models\Project;

/**
 * Check 3 (role permission) then check 4 (project membership). The super admin never reaches this class.
 */
class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('projects.view') || $user->checkPermissionTo('projects.view-all');
    }

    public function view(User $user, Project $project): bool
    {
        return $user->checkPermissionTo('projects.view-all')
            || ($user->checkPermissionTo('projects.view') && $project->hasMember($user));
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('projects.create');
    }

    public function update(User $user, Project $project): bool
    {
        return $user->checkPermissionTo('projects.update') && $this->view($user, $project);
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->checkPermissionTo('projects.delete') && $this->view($user, $project);
    }

    public function manageMembers(User $user, Project $project): bool
    {
        return $user->checkPermissionTo('projects.manage-members') && $this->view($user, $project);
    }
}
