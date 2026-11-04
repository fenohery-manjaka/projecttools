<?php

namespace App\Modules\Tasks\Models;

use App\Modules\Core\Statuses\Status;
use App\Modules\Core\Statuses\StatusCategory;
use App\Modules\Tasks\Enums\TaskStatusCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A task status, configured in Settings › Task statuses. Its order is the order of the Kanban columns.
 *
 * @property TaskStatusCategory $category
 */
class TaskStatus extends Status
{
    public static function categoryEnum(): string
    {
        return TaskStatusCategory::class;
    }

    public static function requiredCategories(): array
    {
        return [TaskStatusCategory::Todo, TaskStatusCategory::Done];
    }

    /**
     * Status of a new or reopened task.
     */
    public static function todo(): self
    {
        return self::firstOf(TaskStatusCategory::Todo) ?? self::query()->ordered()->firstOrFail();
    }

    /**
     * Status of a task ticked as finished.
     */
    public static function done(): self
    {
        return self::firstOf(TaskStatusCategory::Done) ?? self::query()->ordered()->firstOrFail();
    }

    /**
     * Ids of the statuses of the given categories, as a subquery.
     *
     * @return Builder<self>
     */
    public static function idsOf(TaskStatusCategory ...$categories): Builder
    {
        return self::query()->select('id')->whereIn('category', array_map(fn (TaskStatusCategory $category): string => $category->value, $categories));
    }

    /**
     * @return HasMany<Task, $this>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'status_id');
    }

    public function usageCount(): int
    {
        return $this->tasks()->count();
    }

    public function categoryChanged(StatusCategory&\BackedEnum $previous): void
    {
        if ($this->category === TaskStatusCategory::Done) {
            $this->tasks()->whereNull('completed_at')->update(['completed_at' => now()]);
        } elseif ($previous === TaskStatusCategory::Done) {
            $this->tasks()->update(['completed_at' => null]);
        }
    }

    public function reassignTo(Status $replacement): void
    {
        // A task moved to a "done" status gets its end date, and loses it when leaving one.
        $wasDone = $this->category === TaskStatusCategory::Done;
        $isDone = $replacement->category === TaskStatusCategory::Done;

        $this->tasks()->update([
            'status_id' => $replacement->id,
            ...($wasDone === $isDone ? [] : ['completed_at' => $isDone ? now() : null]),
        ]);
    }
}
