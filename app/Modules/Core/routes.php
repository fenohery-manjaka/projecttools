<?php

/*
| Loaded by the ModuleLoader inside the web, auth, verified and module:core middleware.
| Roles and modules are reserved to the super admin (the "administer" gate is false for everyone else).
*/

use App\Modules\Core\Http\Controllers\Settings\MemberController;
use App\Modules\Core\Http\Controllers\Settings\ModuleController;
use App\Modules\Core\Http\Controllers\Settings\RoleController;
use App\Modules\Core\Http\Controllers\Settings\RolePermissionController;
use Illuminate\Support\Facades\Route;

Route::prefix('settings')->name('settings.')->group(function () {
    Route::middleware('can:administer')->group(function () {
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
        Route::patch('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
        Route::put('roles/{role}/permissions', [RolePermissionController::class, 'update'])->name('roles.permissions.update');

        Route::get('modules', [ModuleController::class, 'index'])->name('modules.index');
        Route::patch('modules/{module}', [ModuleController::class, 'update'])->name('modules.update');
    });

    Route::get('members', [MemberController::class, 'index'])->can('members.view')->name('members.index');
    Route::post('members', [MemberController::class, 'store'])->can('members.manage')->name('members.store');
    Route::patch('members/{member}', [MemberController::class, 'update'])->can('members.manage')->name('members.update');
});
