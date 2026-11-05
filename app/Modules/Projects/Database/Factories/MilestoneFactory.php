<?php

namespace App\Modules\Projects\Database\Factories;

use App\Modules\Projects\Models\Milestone;
use App\Modules\Projects\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Milestone>
 */
class MilestoneFactory extends Factory
{
    protected $model = Milestone::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'name' => rtrim(fake()->sentence(2), '.'),
            'due_on' => now()->addWeeks(2)->toDateString(),
            'reached_at' => null,
        ];
    }

    public function reached(): static
    {
        return $this->state(fn (): array => ['reached_at' => now()]);
    }
}
