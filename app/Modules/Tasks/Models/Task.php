<?php

namespace App\Modules\Tasks\Models;

use App\Models\User;
use App\Modules\Projects\Models\Milestone;
use App\Modules\Projects\Models\Project;
use App\Modules\Tasks\Database\Factories\TaskFactory;
use App\Modules\Tasks\Enums\TaskPriority;
use App\Modules\Tasks\Enums\TaskStatusCategory;
use App\Modules\Tasks\Policies\TaskPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property int $project_id
 * @property int|null $milestone_id
 * @property int $status_id
 * @property string $title
 * @property string|null $description
 * @property TaskPriority $priority
 * @property CarbonImmutable|null $due_on
 * @property string|null $estimate_hours
 * @property string|null $blocked_reason
 * @property int $position
 * @property CarbonImmutable|null $completed_at
 * @property int|null $created_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read bool $is_late
 * @property-read Project $project
 * @property-read TaskStatus $status
 */
#[Fillable(['project_id', 'milestone_id', 'status_id', 'title', 'description', 'priority', 'due_on', 'estimate_hours', 'blocked_reason', 'position', 'completed_at', 'created_by'])]
#[UseFactory(TaskFactory::class)]
#[UsePolicy(TaskPolicy::class)]
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'priority' => TaskPriority::class,
            'due_on' => 'date',
            'estimate_hours' => 'decimal:1',
            'position' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return BelongsTo<Milestone, $this>
     */
    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class);
    }

    /**
     * @return BelongsTo<TaskStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(TaskStatus::class, 'status_id');
    }

    /**
     * People responsible for the task.
     *
     * @return BelongsToMany<User, $this>
     */
    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isDone(): bool
    {
        return $this->status->category === TaskStatusCategory::Done;
    }

    /**
     * Past due date and not finished. Load the "status" relation first.
     *
     * @return Attribute<bool, never>
     */
    protected function isLate(): Attribute
    {
        return Attribute::get(fn (): bool => $this->due_on !== null && $this->due_on->lt(today()) && ! $this->isDone());
    }

    /**
     * Tasks of the projects the user may see, archived projects excluded.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function visibleTo(Builder $query, User $user): void
    {
        $query->whereHas('project', fn (Builder $projects) => $projects->visibleTo($user)->whereNull('archived_at'));
    }

    /**
     * Tasks whose status is not of the "done" category.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function open(Builder $query): void
    {
        $query->whereNotIn('status_id', TaskStatus::idsOf(TaskStatusCategory::Done));
    }

    /**
     * Tasks whose status is of one of the given categories.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function inCategory(Builder $query, TaskStatusCategory ...$categories): void
    {
        $query->whereIn('status_id', TaskStatus::idsOf(...$categories));
    }

    /**
     * Open tasks whose due date is past.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function late(Builder $query): void
    {
        $query->open()->whereDate('due_on', '<', today());
    }

    /**
     * Due date first (no date last), then priority.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function byUrgency(Builder $query): void
    {
        $query->orderByRaw('due_on is null')->orderBy('due_on')->byPriority()->orderBy('id');
    }

    /**
     * Most urgent first, as TaskPriority::rank().
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function byPriority(Builder $query): void
    {
        $query->orderByRaw("case priority when 'urgent' then 0 when 'high' then 1 when 'normal' then 2 else 3 end");
    }
}
