<?php

namespace App\Modules\Projects\Database\Factories;

use App\Models\User;
use App\Modules\Projects\Enums\ProjectStatusCategory;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\ProjectStatus;
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
            'status_id' => fn (): int => $this->statusId(ProjectStatusCategory::Active),
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

    public function withStatus(ProjectStatusCategory $category): static
    {
        return $this->state(fn (): array => ['status_id' => $this->statusId($category)]);
    }

    public function archived(): static
    {
        return $this->state(fn (): array => ['archived_at' => now()->subDay()]);
    }

    public function withMember(User $user): static
    {
        return $this->afterCreating(fn (Project $project) => $project->members()->syncWithoutDetaching([$user->id]));
    }

    /**
     * The starting statuses are inserted by the migration, so they exist in every test.
     */
    private function statusId(ProjectStatusCategory $category): int
    {
        return ProjectStatus::firstOf($category)->id ?? ProjectStatus::query()->create([
            'name' => $category->label(), 'tone' => 'secondary', 'category' => $category, 'position' => 99,
        ])->id;
    }
}
