<?php

namespace App\Modules\Tasks\Actions;

use App\Models\User;
use App\Modules\Core\Actions\RecordActivity;
use App\Modules\Tasks\Models\Task;
use App\Modules\Tasks\Models\TaskStatus;
use Illuminate\Support\Facades\DB;

class CreateTask
{
    public function __construct(
        private readonly ChangeTaskStatus $changeTaskStatus,
        private readonly RecordActivity $recordActivity,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes  validated by TaskRequest
     * @param  list<int>  $assigneeIds
     */
    public function handle(User $creator, array $attributes, TaskStatus $status, array $assigneeIds = []): Task
    {
        return DB::transaction(function () use ($creator, $attributes, $status, $assigneeIds): Task {
            $task = new Task(collect($attributes)->except(['status_id', 'blocked_reason'])->all());
            $task->created_by = $creator->id;
            // A new card goes to the bottom of its Kanban column.
            $task->position = (int) Task::query()->where('status_id', $status->id)->max('position') + 1;
            $this->changeTaskStatus->apply($task, $status, $attributes['blocked_reason'] ?? null);
            $task->save();

            $task->assignees()->sync($assigneeIds);

            $this->recordActivity->handle($creator, "a créé la tâche « {$task->title} »", $task);

            return $task;
        });
    }
}
