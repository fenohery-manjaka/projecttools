<?php

namespace App\Modules\Tasks\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Tasks\Actions\MoveTask;
use App\Modules\Tasks\Http\Requests\MoveTaskRequest;
use App\Modules\Tasks\Models\Task;
use Illuminate\Http\RedirectResponse;

/**
 * Drag and drop (or "Move to…") on the Kanban board.
 */
class TaskMoveController extends Controller
{
    public function update(MoveTaskRequest $request, Task $task, MoveTask $moveTask): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $moveTask->handle($user, $task, $request->newStatus(), $request->integer('position'), $request->validated('blocked_reason'));

        return back();
    }
}
