<?php

namespace App\Modules\Tasks\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Tasks\Http\Requests\SubtaskRequest;
use App\Modules\Tasks\Models\Subtask;
use App\Modules\Tasks\Models\Task;
use Illuminate\Http\RedirectResponse;

/**
 * Steps of a task, managed from the task page without reloading it.
 */
class SubtaskController extends Controller
{
    public function store(SubtaskRequest $request, Task $task): RedirectResponse
    {
        $task->subtasks()->create([
            'title' => $request->validated('title'),
            'position' => (int) $task->subtasks()->max('position') + 1,
        ]);

        return back();
    }

    public function update(SubtaskRequest $request, Task $task, Subtask $subtask): RedirectResponse
    {
        $subtask->fill($request->safe()->only('title'));

        if ($request->has('done')) {
            $subtask->done_at = $request->boolean('done') ? ($subtask->done_at ?? now()) : null;
        }

        $subtask->save();

        return back();
    }

    public function destroy(Task $task, Subtask $subtask): RedirectResponse
    {
        $subtask->delete();

        return back();
    }
}
