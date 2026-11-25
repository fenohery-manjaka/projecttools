<?php

namespace App\Modules\Tasks\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Tasks\Support\MyTasks;
use App\Modules\Tasks\Support\TaskPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MyTasksController extends Controller
{
    public function __invoke(Request $request, MyTasks $myTasks, TaskPresenter $presenter): Response
    {
        /** @var User $user */
        $user = $request->user();

        return Inertia::render('tasks/Mine', [
            'tasks' => $myTasks->query($user)->get()->map(fn ($task): array => $presenter->summary($task))->all(),
        ]);
    }
}
