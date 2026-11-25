<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Core\ModuleRegistry;
use App\Modules\Projects\Enums\ProjectStatusCategory;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\ProjectStatus;
use App\Modules\Tasks\Models\Task;
use App\Modules\Tasks\Support\MyTasks;
use App\Modules\Tasks\Support\TaskFormOptions;
use App\Modules\Tasks\Support\TaskPresenter;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(Request $request, ModuleRegistry $registry, MyTasks $myTasks, TaskFormOptions $taskFormOptions, TaskPresenter $presenter): Response
    {
        /** @var User $user */
        $user = $request->user();
        $today = CarbonImmutable::today();
        $attention = $registry->attentionFor($user);
        $agenda = $registry->agendaFor($user, $today, $today->addDays(14))->take(7)->values();
        $activeStatusIds = ProjectStatus::query()
            ->select('id')
            ->where('category', ProjectStatusCategory::Active->value);

        return Inertia::render('Dashboard', [
            'today' => $today->toDateString(),
            'attention' => $attention->map(fn ($item): array => [
                'severity' => $item->severity->value,
                'kind' => $item->kind,
                'label' => $item->label,
                'detail' => $item->detail,
                'url' => $item->url,
                'projectName' => $item->projectName,
                'daysLate' => $item->daysLate,
                'assignees' => $item->assignees,
            ])->all(),
            'summary' => ['count' => $attention->count()],
            'myTasks' => $myTasks->query($user)->limit(6)->get()->map(fn (Task $task): array => $presenter->summary($task))->all(),
            'agenda' => $agenda->map(fn ($item): array => [
                'date' => $item->date,
                'type' => $item->type->value,
                'label' => $item->label,
                'url' => $item->url,
                'projectName' => $item->projectName,
                'isLate' => $item->isLate,
                'isDone' => $item->isDone,
            ])->all(),
            'inProgressProjects' => Project::query()->visibleTo($user)->whereNull('archived_at')->whereIn('status_id', $activeStatusIds)->count(),
            'can' => [
                'createTask' => $user->is_super_admin || $user->checkPermissionTo('tasks.create'),
            ],
            'taskForm' => Inertia::optional(fn (): array => $taskFormOptions->for($user)),
        ]);
    }
}
