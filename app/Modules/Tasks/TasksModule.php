<?php

namespace App\Modules\Tasks;

use App\Models\User;
use App\Modules\Core\Agenda\AgendaItem;
use App\Modules\Core\Agenda\AgendaItemType;
use App\Modules\Core\Agenda\AttentionItem;
use App\Modules\Core\Agenda\Severity;
use App\Modules\Core\Module;
use App\Modules\Tasks\Enums\TaskStatusCategory;
use App\Modules\Tasks\Models\Task;
use App\Modules\Tasks\Models\TaskStatus;
use Carbon\CarbonImmutable;

class TasksModule extends Module
{
    public function key(): string
    {
        return 'tasks';
    }

    public function name(): string
    {
        return 'Tâches';
    }

    public function description(): string
    {
        return 'Tâches, responsables, échéances et sous-tâches.';
    }

    public function isRequired(): bool
    {
        return true;
    }

    public function dependencies(): array
    {
        return ['projects'];
    }

    public function permissions(): array
    {
        return [
            'tasks.view' => 'Voir les tâches des projets visibles',
            'tasks.create' => 'Créer une tâche',
            'tasks.update' => 'Modifier une tâche',
            'tasks.delete' => 'Supprimer une tâche',
            'tasks.comment' => 'Commenter une tâche',
        ];
    }

    /**
     * @return list<AgendaItem>
     */
    public function agenda(User $user, CarbonImmutable $from, CarbonImmutable $to): array
    {
        return array_values(Task::query()
            ->visibleTo($user)
            ->open()
            ->whereBetween('due_on', [$from->toDateString(), $to->toDateString()])
            ->with('project:id,name,color')
            ->orderBy('due_on')
            ->orderBy('id')
            ->get()
            ->map(fn (Task $task): AgendaItem => new AgendaItem(
                $task->due_on->toDateString(), null, AgendaItemType::TaskDue,
                $task->title, route('tasks.show', $task), $task->project->color,
                false, false, $task->project->name,
            ))
            ->values()
            ->all());
    }

    /**
     * @return list<AttentionItem>
     */
    public function attention(User $user): array
    {
        $blockedStatusIds = TaskStatus::idsOf(TaskStatusCategory::Blocked);

        return array_values(Task::query()
            ->visibleTo($user)
            ->where(fn ($query) => $query
                ->whereIn('status_id', $blockedStatusIds)
                ->orWhere(fn ($late) => $late->open()->whereDate('due_on', '<', today())))
            ->with([
                'status',
                'project:id,name,color',
                'assignees' => fn ($query) => $query->select('users.id', 'users.name')->orderBy('name'),
            ])
            ->orderBy('due_on')
            ->orderBy('id')
            ->get()
            ->map(function (Task $task): AttentionItem {
                $blocked = $task->status->category === TaskStatusCategory::Blocked;
                $daysLate = ! $blocked && $task->due_on !== null
                    ? (int) $task->due_on->diffInDays(today())
                    : null;

                return new AttentionItem(
                    severity: $blocked ? Severity::Critical : Severity::Warning,
                    label: $task->title,
                    detail: $blocked ? (string) $task->blocked_reason : '',
                    url: route('tasks.show', $task),
                    projectName: $task->project->name,
                    kind: $blocked ? 'blocked' : 'late',
                    daysLate: $daysLate,
                    assignees: $blocked ? [] : array_values($task->assignees->map(fn (User $assignee): string => $assignee->name)->all()),
                    date: $task->due_on?->toDateString(),
                );
            })
            ->values()
            ->all());
    }
}
