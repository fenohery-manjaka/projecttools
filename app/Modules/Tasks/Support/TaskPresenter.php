<?php

namespace App\Modules\Tasks\Support;

use App\Models\User;
use App\Modules\Tasks\Models\Task;

/**
 * How a task is sent to the pages (list, board, My tasks, home). Load "project", "status"
 * and "assignees" first; "subtasks_count" / "done_subtasks_count" when counted.
 */
class TaskPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function summary(Task $task): array
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'project' => ['id' => $task->project->id, 'name' => $task->project->name, 'color' => $task->project->color],
            'status' => $task->status->toSummary(),
            'priority' => ['value' => $task->priority->value, 'label' => $task->priority->label()],
            'assignees' => $task->assignees->map(fn (User $user): array => ['id' => $user->id, 'name' => $user->name])->values()->all(),
            'due_on' => $task->due_on?->toDateString(),
            'is_late' => $task->is_late,
            'subtasks' => $task->getAttribute('subtasks_count') === null
                ? null
                : ['done' => (int) $task->getAttribute('done_subtasks_count'), 'total' => (int) $task->getAttribute('subtasks_count')],
        ];
    }
}
