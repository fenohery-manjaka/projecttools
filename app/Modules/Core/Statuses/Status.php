<?php

namespace App\Modules\Core\Statuses;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A status the super admin creates, renames, reorders and deletes in the settings
 * (project statuses, task statuses…). Each module extends it with its own table.
 *
 * @property int $id
 * @property string $name
 * @property StatusTone $tone
 * @property StatusCategory&\BackedEnum $category
 * @property int $position
 */
abstract class Status extends Model
{
    /** @var list<string> */
    protected $fillable = ['name', 'tone', 'category', 'position'];

    /**
     * @return class-string<StatusCategory&\BackedEnum>
     */
    abstract public static function categoryEnum(): string;

    /**
     * Categories that must always keep at least one status (the application relies on them).
     *
     * @return list<StatusCategory&\BackedEnum>
     */
    public static function requiredCategories(): array
    {
        return [];
    }

    /**
     * Number of records currently using this status.
     */
    abstract public function usageCount(): int;

    /**
     * Move every record using this status to another one, before deleting it.
     */
    abstract public function reassignTo(self $replacement): void;

    protected function casts(): array
    {
        return [
            'tone' => StatusTone::class,
            'category' => static::categoryEnum(),
            'position' => 'integer',
        ];
    }

    /**
     * First status of a category, in the order chosen in the settings.
     */
    public static function firstOf(StatusCategory&\BackedEnum $category): ?static
    {
        return static::query()->where('category', $category->value)->ordered()->first();
    }

    /**
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('position')->orderBy('id');
    }

    /**
     * @return array{id: int, name: string, tone: string, category: string}
     */
    public function toSummary(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'tone' => $this->tone->value,
            // Category enums are string-backed (see StatusCategory).
            'category' => (string) $this->category->value,
        ];
    }
}
