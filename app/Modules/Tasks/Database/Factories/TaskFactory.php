<?php

namespace App\Modules\Tasks\Database\Factories;

use App\Models\User;
use App\Modules\Projects\Models\Project;
use App\Modules\Tasks\Enums\TaskPriority;
use App\Modules\Tasks\Enums\TaskStatusCategory;
use App\Modules\Tasks\Models\Task;
use App\Modules\Tasks\Models\TaskStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'status_id' => fn (): int => TaskStatus::todo()->id,
            'title' => rtrim(fake()->sentence(4), '.'),
            'description' => null,
            'priority' => TaskPriority::Normal,
            'due_on' => null,
            'position' => 0,
        ];
    }

    /**
     * Uses the first status of the category, as the application does (the starting statuses come from the migration).
     */
    public function inCategory(TaskStatusCategory $category): static
    {
        return $this->state(function () use ($category): array {
            $status = TaskStatus::firstOf($category) ?? TaskStatus::query()->create([
                'name' => $category->label(), 'tone' => 'secondary', 'category' => $category, 'position' => 99,
            ]);

            return [
                'status_id' => $status->id,
                'completed_at' => $category === TaskStatusCategory::Done ? now() : null,
                'blocked_reason' => $category === TaskStatusCategory::Blocked ? 'En attente d’une validation.' : null,
            ];
        });
    }

    public function done(): static
    {
        return $this->inCategory(TaskStatusCategory::Done);
    }

    public function blocked(): static
    {
        return $this->inCategory(TaskStatusCategory::Blocked);
    }

    public function dueOn(string $date): static
    {
        return $this->state(fn (): array => ['due_on' => $date]);
    }

    public function priority(TaskPriority $priority): static
    {
        return $this->state(fn (): array => ['priority' => $priority]);
    }

    public function assignedTo(User ...$users): static
    {
        return $this->afterCreating(fn (Task $task) => $task->assignees()->syncWithoutDetaching(array_map(fn (User $user): int => $user->id, $users)));
    }
}
