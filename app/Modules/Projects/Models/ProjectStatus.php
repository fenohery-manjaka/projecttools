<?php

namespace App\Modules\Projects\Models;

use App\Modules\Core\Statuses\Status;
use App\Modules\Projects\Enums\ProjectStatusCategory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A project status, configured in Settings › Project statuses.
 *
 * @property ProjectStatusCategory $category
 */
class ProjectStatus extends Status
{
    public static function categoryEnum(): string
    {
        return ProjectStatusCategory::class;
    }

    /**
     * Status given to a new project: the first "planned" one, else the first of the list.
     */
    public static function default(): self
    {
        return self::firstOf(ProjectStatusCategory::Planned) ?? self::query()->ordered()->firstOrFail();
    }

    /**
     * @return HasMany<Project, $this>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'status_id');
    }

    public function usageCount(): int
    {
        return $this->projects()->count();
    }

    public function reassignTo(Status $replacement): void
    {
        $this->projects()->update(['status_id' => $replacement->id]);
    }
}
