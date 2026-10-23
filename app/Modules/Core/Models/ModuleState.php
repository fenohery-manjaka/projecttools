<?php

namespace App\Modules\Core\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Whether a module is enabled. A module without a row is enabled.
 *
 * @property string $key
 * @property bool $enabled
 * @property array<string, mixed>|null $settings
 */
#[Table('modules', key: 'key', keyType: 'string', incrementing: false)]
#[Fillable(['key', 'enabled', 'settings'])]
class ModuleState extends Model
{
    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'settings' => 'array',
        ];
    }
}
