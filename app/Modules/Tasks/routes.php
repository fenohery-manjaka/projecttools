<?php

/*
| Loaded by the ModuleLoader inside the web, auth, verified and module:tasks middleware.
| Every route declares its access check with ->can() — a test fails otherwise.
*/

use App\Modules\Tasks\Http\Controllers\Settings\TaskStatusController as TaskStatusSettingsController;
use App\Modules\Tasks\Http\Controllers\SubtaskController;
use App\Modules\Tasks\Http\Controllers\TaskController;
use App\Modules\Tasks\Http\Controllers\TaskMoveController;
use App\Modules\Tasks\Http\Controllers\TaskStatusController;
use App\Modules\Tasks\Models\Task;
use Illuminate\Support\Facades\Route;

// No "tasks/create" page: tasks are created in a dialog (docs/conventions.md).
// The project comes from the request, so TaskRequest checks "create" on it.
Route::get('tasks', [TaskController::class, 'index'])->can('viewAny', Task::class)->name('tasks.index');
Route::post('tasks', [TaskController::class, 'store'])->can('viewAny', Task::class)->name('tasks.store');

// {task} is numeric, so "tasks/create" and friends answer 404 instead of 405.
Route::where(['task' => '[0-9]+'])->group(function () {
    Route::get('tasks/{task}', [TaskController::class, 'show'])->can('view', 'task')->name('tasks.show');
    Route::get('tasks/{task}/edit', [TaskController::class, 'edit'])->can('update', 'task')->name('tasks.edit');
    Route::put('tasks/{task}', [TaskController::class, 'update'])->can('update', 'task')->name('tasks.update');
    Route::patch('tasks/{task}/status', [TaskStatusController::class, 'update'])->can('update', 'task')->name('tasks.status.update');
    Route::patch('tasks/{task}/move', [TaskMoveController::class, 'update'])->can('update', 'task')->name('tasks.move');
    Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->can('delete', 'task')->name('tasks.destroy');

    Route::scopeBindings()->group(function () {
        Route::post('tasks/{task}/subtasks', [SubtaskController::class, 'store'])->can('update', 'task')->name('tasks.subtasks.store');
        Route::patch('tasks/{task}/subtasks/{subtask}', [SubtaskController::class, 'update'])->can('update', 'task')->name('tasks.subtasks.update');
        Route::delete('tasks/{task}/subtasks/{subtask}', [SubtaskController::class, 'destroy'])->can('update', 'task')->name('tasks.subtasks.destroy');
    });
});

// Settings › Task statuses, reserved to the super admin like roles and modules.
Route::prefix('settings/task-statuses')->name('settings.task-statuses.')->controller(TaskStatusSettingsController::class)->group(function () {
    Route::get('/', 'index')->can('administer')->name('index');
    Route::post('/', 'store')->can('administer')->name('store');
    Route::patch('{status}', 'update')->can('administer')->whereNumber('status')->name('update');
    Route::patch('{status}/move', 'move')->can('administer')->whereNumber('status')->name('move');
    Route::delete('{status}', 'destroy')->can('administer')->whereNumber('status')->name('destroy');
});
