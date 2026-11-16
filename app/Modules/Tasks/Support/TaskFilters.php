<?php

namespace App\Modules\Tasks\Support;

use App\Modules\Tasks\Enums\TaskStatusCategory;
use App\Modules\Tasks\Models\Task;
use Illuminate\Database\Eloquent\Builder;

/**
 * The filters of the task list and of the Kanban board, written once.
 * Values come from TaskFilterRequest::filters().
 */
class TaskFilters
{
    public const array DUE_OPTIONS = [
        'late' => 'En retard',
        'week' => '7 prochains jours',
        'blocked' => 'Bloquées',
        'none' => 'Sans échéance',
    ];

    /**
     * @param  Builder<Task>  $query
     * @param  array{project?: int|null, member?: int|null, priority?: string|null, due?: string|null, q?: string|null}  $filters
     * @return Builder<Task>
     */
    public function apply(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['project'] ?? null, fn (Builder $query, int $project) => $query->where('project_id', $project))
            ->when($filters['member'] ?? null, fn (Builder $query, int $member) => $query->whereHas('assignees', fn (Builder $users) => $users->whereKey($member)))
            ->when($filters['priority'] ?? null, fn (Builder $query, string $priority) => $query->where('priority', $priority))
            ->when($filters['due'] ?? null, fn (Builder $query, string $due) => match ($due) {
                'late' => $query->late(),
                'week' => $query->open()->whereDate('due_on', '>=', today())->whereDate('due_on', '<=', today()->addDays(7)),
                'blocked' => $query->inCategory(TaskStatusCategory::Blocked),
                'none' => $query->whereNull('due_on'),
                default => $query,
            })
            ->when($filters['q'] ?? null, fn (Builder $query, string $search) => $query->where(fn (Builder $query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")));
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function isActive(array $filters): bool
    {
        return collect($filters)->only(['project', 'member', 'priority', 'due', 'q'])->filter(fn (mixed $value): bool => filled($value))->isNotEmpty();
    }
}
