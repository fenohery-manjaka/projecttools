<?php

namespace App\Modules\Tasks\Support;

use App\Models\User;
use App\Modules\Tasks\Models\Task;
use Illuminate\Database\Eloquent\Builder;
<<<<<<< HEAD

class MyTasks
{
=======
use Illuminate\Database\Eloquent\Collection;

/**
 * The rule of "my open tasks": tasks I am responsible for, not finished, in visible and
 * not archived projects, ranked by urgency. Shared by My tasks and, later, the home page.
 */
class MyTasks
{
    public const array GROUPS = [
        'late' => 'En retard',
        'today' => 'Aujourd’hui',
        'week' => 'Cette semaine',
        'later' => 'Plus tard',
        'none' => 'Sans échéance',
    ];

>>>>>>> 1262d364762aee46cda10c0c526e9a58395f4ee8
    /**
     * @return Builder<Task>
     */
    public function query(User $user): Builder
    {
<<<<<<< HEAD
        $today = today()->toDateString();
        $endOfWeek = today()->addDays(7)->toDateString();

        return Task::query()
            ->visibleTo($user)
            ->open()
            ->whereHas('assignees', fn (Builder $query) => $query->whereKey($user->id))
            ->with(['project:id,name,color', 'status', 'assignees:id,name'])
            ->orderByRaw(
                'case when due_on < ? then 0 when due_on = ? then 1 when due_on <= ? then 2 when due_on is null then 4 else 3 end',
                [$today, $today, $endOfWeek],
            )
            ->byPriority()
            ->orderBy('due_on')
            ->orderBy('id');
=======
        return Task::query()
            ->visibleTo($user)
            ->whereHas('assignees', fn (Builder $users) => $users->whereKey($user->id));
    }

    /**
     * Open tasks, most urgent first: by group, then priority, then due date.
     *
     * @return Collection<int, Task>
     */
    public function open(User $user, ?int $limit = null): Collection
    {
        $tasks = $this->query($user)
            ->open()
            ->with(['project:id,name,color', 'status', 'assignees:id,name'])
            ->get();

        $sorted = $tasks->sortBy([
            fn (Task $a, Task $b): int => array_search($this->group($a), array_keys(self::GROUPS), true) <=> array_search($this->group($b), array_keys(self::GROUPS), true),
            fn (Task $a, Task $b): int => $a->priority->rank() <=> $b->priority->rank(),
            fn (Task $a, Task $b): int => ($a->due_on->timestamp ?? PHP_INT_MAX) <=> ($b->due_on->timestamp ?? PHP_INT_MAX),
            fn (Task $a, Task $b): int => $a->id <=> $b->id,
        ])->values();

        return $limit === null ? $sorted : $sorted->take($limit)->values();
    }

    /**
     * Key of GROUPS the task falls in.
     */
    public function group(Task $task): string
    {
        $today = today();

        return match (true) {
            $task->due_on === null => 'none',
            $task->due_on->lt($today) => 'late',
            $task->due_on->isSameDay($today) => 'today',
            $task->due_on->lte($today->addDays(7)) => 'week',
            default => 'later',
        };
    }

    /**
     * @return Collection<int, Task>
     */
    public function recentlyDone(User $user, int $limit = 5): Collection
    {
        return $this->query($user)
            ->whereNotNull('completed_at')
            ->whereNotIn('id', $this->query($user)->open()->select('id'))
            ->with(['project:id,name,color', 'status', 'assignees:id,name'])
            ->latest('completed_at')
            ->latest('id')
            ->limit($limit)
            ->get();
>>>>>>> 1262d364762aee46cda10c0c526e9a58395f4ee8
    }
}
