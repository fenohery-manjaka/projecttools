<?php

namespace App\Modules\Tasks\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Tasks\Actions\ChangeTaskStatus;
use App\Modules\Tasks\Http\Requests\UpdateTaskStatusRequest;
use App\Modules\Tasks\Models\Task;
use Illuminate\Http\RedirectResponse;

/**
 * Quick status change: buttons of the task page, ticking a task in My tasks.
 */
class TaskStatusController extends Controller
{
    public function update(UpdateTaskStatusRequest $request, Task $task, ChangeTaskStatus $changeTaskStatus): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $changeTaskStatus->handle($user, $task, $request->newStatus(), $request->validated('blocked_reason'));

        return back();
    }
}
