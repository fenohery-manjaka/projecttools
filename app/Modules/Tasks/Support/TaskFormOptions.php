<?php

namespace App\Modules\Tasks\Support;

use App\Models\User;
use App\Modules\Projects\Models\Milestone;
use App\Modules\Projects\Models\Project;
use App\Modules\Tasks\Enums\TaskPriority;
use App\Modules\Tasks\Models\TaskStatus;

/**
 * Options of the task form, shared by the "New task" dialog of every page and the edit page.
 */
class TaskFormOptions
{
    /**
     * @return array<string, mixed>
     */
    public function for(User $user, ?Project $preselected = null, ?Project $alsoInclude = null): array
    {
        $projects = $this->projects($user, $alsoInclude);

        $projectId = $preselected !== null && $projects->contains('id', $preselected->id)
            ? $preselected->id
            : $projects->first()?->id;

        return [
            'projects' => $projects->map(fn (Project $project): array => [
                'id' => $project->id,
                'name' => $project->name,
                'color' => $project->color,
                'members' => $project->members->map(fn (User $member): array => ['id' => $member->id, 'name' => $member->name])->values()->all(),
                'milestones' => $project->milestones->map(fn (Milestone $milestone): array => ['id' => $milestone->id, 'name' => $milestone->name])->values()->all(),
            ])->values()->all(),
            'statuses' => TaskStatus::query()->ordered()->get()->map(fn (TaskStatus $status): array => $status->toSummary())->values()->all(),
            'priorities' => TaskPriority::options(),
            'defaults' => [
                'project_id' => $projectId,
                'status_id' => TaskStatus::todo()->id,
                'priority' => TaskPriority::Normal->value,
                'assignee_ids' => [],
            ],
        ];
    }

    /**
     * Visible, not archived projects where the user may create a task.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Project>
     */
    private function projects(User $user, ?Project $alsoInclude): \Illuminate\Database\Eloquent\Collection
    {
        $canCreate = $user->is_super_admin || $user->checkPermissionTo('tasks.create');

        if (! $canCreate && $alsoInclude === null) {
            return new \Illuminate\Database\Eloquent\Collection;
        }

        return Project::query()
            ->visibleTo($user)
            ->where(fn ($query) => $canCreate
                ? $query->whereNull('archived_at')->when($alsoInclude, fn ($query, Project $project) => $query->orWhereKey($project->id))
                : $query->whereKey($alsoInclude->id))
            ->with([
                'members' => fn ($query) => $query->select('users.id', 'users.name')->whereNull('deactivated_at')->orderBy('name'),
                'milestones:id,project_id,name,due_on',
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'color', 'archived_at', 'owner_id']);
    }
}
