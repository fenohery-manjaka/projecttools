<?php

namespace App\Modules\Projects\Models;

use App\Modules\Projects\Database\Factories\MilestoneFactory;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A dated step of a project ("Maquette validée · 12 novembre").
 *
 * @property int $id
 * @property int $project_id
 * @property string $name
 * @property CarbonImmutable $due_on
 * @property CarbonImmutable|null $reached_at
 * @property-read bool $is_overdue
 */
#[Fillable(['name', 'due_on', 'reached_at'])]
#[UseFactory(MilestoneFactory::class)]
class Milestone extends Model
{
    /** @use HasFactory<MilestoneFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'due_on' => 'date',
            'reached_at' => 'datetime',
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
     * Not reached and its date is past.
     *
     * @return Attribute<bool, never>
     */
    protected function isOverdue(): Attribute
    {
        return Attribute::get(fn (): bool => $this->reached_at === null && $this->due_on->lt(today()));
    }
}
