<?php

namespace App\Modules\Tasks\Actions;

use App\Models\User;
use App\Modules\Tasks\Models\Task;
use App\Modules\Tasks\Models\TaskStatus;
use Illuminate\Support\Facades\DB;

/**
 * Drops a card of the Kanban board in a column (status) at a position, and renumbers that column.
 */
class MoveTask
{
    public function __construct(private readonly ChangeTaskStatus $changeTaskStatus) {}

    public function handle(User $user, Task $task, TaskStatus $status, int $position, ?string $blockedReason = null): void
    {
        DB::transaction(function () use ($user, $task, $status, $position, $blockedReason): void {
            $previousStatusId = $task->status_id;

            $this->changeTaskStatus->handle($user, $task, $status, $blockedReason ?? ($task->status_id === $status->id ? $task->blocked_reason : null));

            $column = Task::query()
                ->where('status_id', $status->id)
                ->whereKeyNot($task->id)
                ->orderBy('position')
                ->orderBy('id')
                ->pluck('id')
                ->all();

            array_splice($column, max(0, min($position, count($column))), 0, [$task->id]);
            $this->renumber($column);

            if ($previousStatusId !== $status->id) {
                $this->renumber(Task::query()->where('status_id', $previousStatusId)->orderBy('position')->orderBy('id')->pluck('id')->all());
            }
        });
    }

    /**
     * @param  array<int, int>  $ids  in their new order
     */
    private function renumber(array $ids): void
    {
        foreach (array_values($ids) as $position => $id) {
            Task::query()->whereKey($id)->where('position', '!=', $position)->update(['position' => $position]);
        }
    }
}
