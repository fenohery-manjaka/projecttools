<?php

namespace App\Modules\Tasks\Models;

use App\Modules\Tasks\Database\Factories\SubtaskFactory;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A small step of a task, ticked when done ("Sous-tâches et vérifications").
 *
 * @property int $id
 * @property int $task_id
 * @property string $title
 * @property CarbonImmutable|null $done_at
 * @property int $position
 */
#[Fillable(['title', 'done_at', 'position'])]
#[UseFactory(SubtaskFactory::class)]
class Subtask extends Model
{
    /** @use HasFactory<SubtaskFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'done_at' => 'datetime',
            'position' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Task, $this>
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
