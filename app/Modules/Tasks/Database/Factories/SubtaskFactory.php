<?php

namespace App\Modules\Tasks\Database\Factories;

use App\Modules\Tasks\Models\Subtask;
use App\Modules\Tasks\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subtask>
 */
class SubtaskFactory extends Factory
{
    protected $model = Subtask::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'title' => rtrim(fake()->sentence(3), '.'),
            'done_at' => null,
            'position' => 0,
        ];
    }

    public function done(): static
    {
        return $this->state(fn (): array => ['done_at' => now()]);
    }
}
