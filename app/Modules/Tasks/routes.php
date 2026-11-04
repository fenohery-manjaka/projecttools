<?php

/*
| Loaded by the ModuleLoader inside the web, auth, verified and module:tasks middleware.
| Every route declares its access check with ->can() — a test fails otherwise.
*/

use App\Modules\Tasks\Http\Controllers\Settings\TaskStatusController;
use Illuminate\Support\Facades\Route;

// Settings › Task statuses, reserved to the super admin like roles and modules.
Route::prefix('settings/task-statuses')->name('settings.task-statuses.')->controller(TaskStatusController::class)->group(function () {
    Route::get('/', 'index')->can('administer')->name('index');
    Route::post('/', 'store')->can('administer')->name('store');
    Route::patch('{status}', 'update')->can('administer')->whereNumber('status')->name('update');
    Route::patch('{status}/move', 'move')->can('administer')->whereNumber('status')->name('move');
    Route::delete('{status}', 'destroy')->can('administer')->whereNumber('status')->name('destroy');
});
