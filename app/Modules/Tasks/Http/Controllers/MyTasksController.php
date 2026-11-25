<?php

namespace App\Modules\Tasks\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
<<<<<<< HEAD
use App\Modules\Tasks\Support\MyTasks;
=======
use App\Modules\Tasks\Enums\TaskPriority;
use App\Modules\Tasks\Models\Task;
use App\Modules\Tasks\Models\TaskStatus;
use App\Modules\Tasks\Support\MyTasks;
use App\Modules\Tasks\Support\TaskFormOptions;
>>>>>>> 1262d364762aee46cda10c0c526e9a58395f4ee8
use App\Modules\Tasks\Support\TaskPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MyTasksController extends Controller
{
<<<<<<< HEAD
    public function __invoke(Request $request, MyTasks $myTasks, TaskPresenter $presenter): Response
=======
    public function index(Request $request, MyTasks $myTasks, TaskPresenter $presenter, TaskFormOptions $taskFormOptions): Response
>>>>>>> 1262d364762aee46cda10c0c526e9a58395f4ee8
    {
        /** @var User $user */
        $user = $request->user();

<<<<<<< HEAD
        return Inertia::render('tasks/Mine', [
            'tasks' => $myTasks->query($user)->get()->map(fn ($task): array => $presenter->summary($task))->all(),
=======
        $open = $myTasks->open($user);

        $groups = collect(MyTasks::GROUPS)
            ->map(fn (string $label, string $key): array => [
                'key' => $key,
                'label' => $label,
                'tasks' => $open->filter(fn (Task $task): bool => $myTasks->group($task) === $key)->map(fn (Task $task): array => $presenter->summary($task))->values(),
            ])
            ->filter(fn (array $group): bool => $group['tasks']->isNotEmpty())
            ->values();

        $canTick = $user->is_super_admin || $user->checkPermissionTo('tasks.update');

        return Inertia::render('tasks/Mine', [
            'groups' => $groups,
            'openCount' => $open->count(),
            'recentlyDone' => $myTasks->recentlyDone($user)->map(fn (Task $task): array => $presenter->summary($task))->values(),
            'load' => [
                'byPriority' => collect(TaskPriority::cases())->map(fn (TaskPriority $priority): array => [
                    'value' => $priority->value,
                    'label' => $priority->label(),
                    'count' => $open->filter(fn (Task $task): bool => $task->priority === $priority)->count(),
                ])->values(),
                'remainingHours' => round((float) $open->sum(fn (Task $task): float => (float) $task->estimate_hours), 1),
            ],
            'tickStatuses' => $canTick ? ['done' => TaskStatus::done()->id, 'todo' => TaskStatus::todo()->id] : null,
            'can' => [
                'create' => $user->is_super_admin || $user->checkPermissionTo('tasks.create'),
            ],
            'taskForm' => Inertia::optional(fn (): array => $taskFormOptions->for($user)),
>>>>>>> 1262d364762aee46cda10c0c526e9a58395f4ee8
        ]);
    }
}
