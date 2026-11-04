<?php

/*
| Loaded by the ModuleLoader inside the web, auth, verified and module:projects middleware.
| Every route declares its access check with ->can() — a test fails otherwise.
*/

use App\Modules\Projects\Http\Controllers\ProjectController;
use App\Modules\Projects\Http\Controllers\ProjectMemberController;
use App\Modules\Projects\Http\Controllers\Settings\ProjectStatusController;
use App\Modules\Projects\Models\Project;
use Illuminate\Support\Facades\Route;

Route::get('projects', [ProjectController::class, 'index'])->can('viewAny', Project::class)->name('projects.index');
Route::post('projects', [ProjectController::class, 'store'])->can('create', Project::class)->name('projects.store');
Route::get('projects/{project}', [ProjectController::class, 'show'])->can('view', 'project')->name('projects.show');
Route::get('projects/{project}/edit', [ProjectController::class, 'edit'])->can('update', 'project')->name('projects.edit');
Route::put('projects/{project}', [ProjectController::class, 'update'])->can('update', 'project')->name('projects.update');
Route::delete('projects/{project}', [ProjectController::class, 'destroy'])->can('delete', 'project')->name('projects.destroy');

Route::post('projects/{project}/members', [ProjectMemberController::class, 'store'])->can('manageMembers', 'project')->name('projects.members.store');
Route::delete('projects/{project}/members/{member}', [ProjectMemberController::class, 'destroy'])->can('manageMembers', 'project')->name('projects.members.destroy');

// Settings › Project statuses, reserved to the super admin like roles and modules.
Route::prefix('settings/project-statuses')->name('settings.project-statuses.')->controller(ProjectStatusController::class)->group(function () {
    Route::get('/', 'index')->can('administer')->name('index');
    Route::post('/', 'store')->can('administer')->name('store');
    Route::patch('{status}', 'update')->can('administer')->whereNumber('status')->name('update');
    Route::patch('{status}/move', 'move')->can('administer')->whereNumber('status')->name('move');
    Route::delete('{status}', 'destroy')->can('administer')->whereNumber('status')->name('destroy');
});
