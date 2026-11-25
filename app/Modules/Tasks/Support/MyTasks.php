<?php

namespace App\Modules\Tasks\Support;

use App\Models\User;
use App\Modules\Tasks\Models\Task;
use Illuminate\Database\Eloquent\Builder;

class MyTasks
{
    /**
     * @return Builder<Task>
     */
    public function query(User $user): Builder
    {
        $today = today()->toDateString();
        $endOfWeek = today()->addDays(7)->toDateString();

        return Task::query()
            ->visibleTo($user)
            ->open()
            ->whereHas('assignees', fn (Builder $query) => $query->whereKey($user->id))
            ->with(['project:id,name,color', 'status', 'assignees:id,name'])
            ->orderByRaw(
                'case when due_on < ? then 0 when due_on = ? then 1 when due_on <= ? then 2 when due_on is null then 4 else 3 end',
                [$today, $today, $endOfWeek],
            )
            ->byPriority()
            ->orderBy('due_on')
            ->orderBy('id');
    }
}
