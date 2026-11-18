<?php

namespace App\Modules\Tasks\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Actions\PostComment;
use App\Modules\Tasks\Http\Requests\StoreCommentRequest;
use App\Modules\Tasks\Models\Task;
use Illuminate\Http\RedirectResponse;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, Task $task, PostComment $postComment): RedirectResponse
    {
        $allowedUserIds = $task->project->members()
            ->whereNull('deactivated_at')
            ->pluck('users.id')
            ->push($task->project->owner_id)
            ->all();

        $postComment->handle(
            $task,
            $request->user(),
            $request->validated('body'),
            $request->validated('mentioned_user_ids', []),
            $allowedUserIds,
        );

        return back();
    }
}
