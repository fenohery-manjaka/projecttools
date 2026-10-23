<?php

namespace App\Modules\Projects\Database\Factories;

use App\Models\User;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => rtrim(fake()->unique()->sentence(3), '.'),
            'description' => fake()->sentence(),
            'objectives' => null,
            'status' => ProjectStatus::Active,
            'owner_id' => User::factory(),
            'color' => fake()->randomElement(Project::COLORS),
            'starts_on' => now()->subWeek(),
            'ends_on' => now()->addMonth(),
        ];
    }

    /**
     * Like CreateProject, the owner always belongs to the project.
     */
    public function configure(): static
    {
        return $this->afterCreating(fn (Project $project) => $project->members()->syncWithoutDetaching([$project->owner_id]));
    }

    public function withMember(User $user): static
    {
        return $this->afterCreating(fn (Project $project) => $project->members()->syncWithoutDetaching([$user->id]));
    }
}
