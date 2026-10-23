<?php

namespace App\Modules\Core\Actions;

use App\Models\User;
use App\Modules\Core\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class RecordActivity
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public function handle(?User $user, string $description, ?Model $subject = null, array $properties = []): ActivityLog
    {
        return ActivityLog::query()->create([
            'user_id' => $user?->id,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'properties' => $properties === [] ? null : $properties,
        ]);
    }
}
