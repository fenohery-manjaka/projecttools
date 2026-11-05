<?php

namespace App\Modules\Tasks\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Core\Actions\RecordActivity;
use App\Modules\Tasks\Actions\ChangeTaskStatus;
use App\Modules\Tasks\Actions\CreateTask;
use App\Modules\Tasks\Http\Requests\TaskRequest;
use App\Modules\Tasks\Models\Task;
use App\Modules\Tasks\Models\TaskStatus;
use App\Modules\Tasks\Support\TaskFormOptions;
use App\Modules\Tasks\Support\TaskPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * There is no "create" page: a task is created in the dialog of the page the user is on
 * (docs/conventions.md, « Créer dans une modale »), which then comes back to that page.
 */
class TaskController extends Controller
{
    public function store(TaskRequest $request, CreateTask $createTask): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $createTask->handle($user, $request->safe()->except('assignee_ids'), $request->taskStatus(), $request->assigneeIds());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Tâche créée.']);

        return back();
    }

    public function show(Request $request, Task $task, TaskPresenter $presenter): Response
    {
        /** @var User $user */
        $user = $request->user();

        $task->load(['project:id,name,color,archived_at', 'status', 'milestone:id,name,due_on', 'assignees:id,name', 'creator:id,name']);

        return Inertia::render('tasks/Show', [
            'task' => [
                ...$presenter->summary($task),
                'description' => $task->description,
                'blocked_reason' => $task->blocked_reason,
                'estimate_hours' => $task->estimate_hours,
                'milestone' => $task->milestone === null ? null : [
                    'id' => $task->milestone->id,
                    'name' => $task->milestone->name,
                    'due_on' => $task->milestone->due_on->toDateString(),
                ],
                'creator' => $task->creator?->name,
                'created_at' => $task->created_at?->toIso8601String(),
                'completed_at' => $task->completed_at?->toIso8601String(),
            ],
            'statuses' => TaskStatus::query()->ordered()->get()->map(fn (TaskStatus $status): array => $status->toSummary()),
            'can' => [
                'update' => $user->can('update', $task),
                'delete' => $user->can('delete', $task),
            ],
        ]);
    }

    public function edit(Request $request, Task $task, TaskFormOptions $options): Response
    {
        /** @var User $user */
        $user = $request->user();

        $task->load('assignees:id');

        return Inertia::render('tasks/Edit', [
            ...$options->for($user, $task->project, alsoInclude: $task->project),
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'project_id' => $task->project_id,
                'milestone_id' => $task->milestone_id,
                'status_id' => $task->status_id,
                'priority' => $task->priority->value,
                'assignee_ids' => $task->assignees->modelKeys(),
                'due_on' => $task->due_on?->toDateString(),
                'estimate_hours' => $task->estimate_hours,
                'description' => $task->description,
                'blocked_reason' => $task->blocked_reason,
            ],
        ]);
    }

    public function update(TaskRequest $request, Task $task, ChangeTaskStatus $changeTaskStatus, RecordActivity $recordActivity): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        DB::transaction(function () use ($request, $task, $changeTaskStatus, $recordActivity, $user): void {
            $task->fill($request->safe()->except(['assignee_ids', 'status_id', 'blocked_reason']));

            $statusChanged = $task->status_id !== $request->integer('status_id');
            $changeTaskStatus->apply($task, $request->taskStatus(), $request->validated('blocked_reason'));
            $task->save();
            $task->assignees()->sync($request->assigneeIds());

            $recordActivity->handle($user, $statusChanged
                ? "a modifié la tâche « {$task->title} » et l’a passée à « {$task->status->name} »"
                : "a modifié la tâche « {$task->title} »", $task);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Tâche enregistrée.']);

        return to_route('tasks.show', $task);
    }

    public function destroy(Request $request, Task $task, RecordActivity $recordActivity): RedirectResponse
    {
        $project = $task->project;
        $task->delete();

        $recordActivity->handle($request->user(), "a supprimé la tâche « {$task->title} »", $project);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Tâche supprimée.']);

        return to_route('projects.show', $project);
    }
}
