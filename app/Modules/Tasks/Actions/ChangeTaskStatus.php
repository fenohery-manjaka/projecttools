<?php

namespace App\Modules\Tasks\Actions;

use App\Models\User;
use App\Modules\Core\Actions\RecordActivity;
use App\Modules\Tasks\Enums\TaskStatusCategory;
use App\Modules\Tasks\Models\Task;
use App\Modules\Tasks\Models\TaskStatus;

/**
 * Applies the rules of a status to a task: a "done" status fills completed_at,
 * a "blocked" one keeps the reason, any other clears it. Does not save.
 */
class ChangeTaskStatus
{
    public function __construct(private readonly RecordActivity $recordActivity) {}

    public function apply(Task $task, TaskStatus $status, ?string $blockedReason = null): void
    {
        $task->status_id = $status->id;
        $task->setRelation('status', $status);

        $task->blocked_reason = $status->category === TaskStatusCategory::Blocked ? $blockedReason : null;

        if ($status->category === TaskStatusCategory::Done) {
            $task->completed_at ??= now();
        } else {
            $task->completed_at = null;
        }
    }

    /**
     * Change, save and log.
     */
    public function handle(User $user, Task $task, TaskStatus $status, ?string $blockedReason = null): void
    {
        $changed = $task->status_id !== $status->id;

        $this->apply($task, $status, $blockedReason);
        $task->save();

        if ($changed) {
            $this->recordActivity->handle($user, "a passé la tâche « {$task->title} » à « {$status->name} »", $task);
        }
    }
}
