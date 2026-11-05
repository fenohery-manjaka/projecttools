<?php

/*
| Loaded by the ModuleLoader inside the web, auth, verified and module:tasks middleware.
| Every route declares its access check with ->can() — a test fails otherwise.
*/

use App\Modules\Tasks\Http\Controllers\Settings\TaskStatusController as TaskStatusSettingsController;
use App\Modules\Tasks\Http\Controllers\TaskController;
use App\Modules\Tasks\Http\Controllers\TaskStatusController;
use App\Modules\Tasks\Models\Task;
use Illuminate\Support\Facades\Route;

// No "tasks/create" page: tasks are created in a dialog (docs/conventions.md).
// The project comes from the request, so TaskRequest checks "create" on it.
Route::post('tasks', [TaskController::class, 'store'])->can('viewAny', Task::class)->name('tasks.store');
Route::get('tasks/{task}', [TaskController::class, 'show'])->can('view', 'task')->whereNumber('task')->name('tasks.show');
Route::get('tasks/{task}/edit', [TaskController::class, 'edit'])->can('update', 'task')->name('tasks.edit');
Route::put('tasks/{task}', [TaskController::class, 'update'])->can('update', 'task')->name('tasks.update');
Route::patch('tasks/{task}/status', [TaskStatusController::class, 'update'])->can('update', 'task')->name('tasks.status.update');
Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->can('delete', 'task')->name('tasks.destroy');

// Settings › Task statuses, reserved to the super admin like roles and modules.
Route::prefix('settings/task-statuses')->name('settings.task-statuses.')->controller(TaskStatusSettingsController::class)->group(function () {
    Route::get('/', 'index')->can('administer')->name('index');
    Route::post('/', 'store')->can('administer')->name('store');
    Route::patch('{status}', 'update')->can('administer')->whereNumber('status')->name('update');
    Route::patch('{status}/move', 'move')->can('administer')->whereNumber('status')->name('move');
    Route::delete('{status}', 'destroy')->can('administer')->whereNumber('status')->name('destroy');
});
