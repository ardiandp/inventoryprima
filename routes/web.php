<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Rute aplikasi diawali dari PHASE 0 (beranda/layout AdminLTE 3).
| PHASE 2: Auth (login/logout), manajemen user, role, audit log.
|
*/

// --- Guest routes ---
Route::get('/', function () {
    return redirect()->route('home');
});

Route::get('/login', [LoginController::class, 'create'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'store'])->middleware('guest');

// --- Authenticated routes ---
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/home', function () {
        return view('home');
    })->name('home');

    // User management (super_admin)
    Route::middleware('permission:user.manage')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // Role & permission management (super_admin)
    Route::middleware('permission:role.manage')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    Route::get('/permissions', [RoleController::class, 'permissions'])->name('permissions.index')
        ->middleware('permission:role.manage');

    // Audit log (super_admin)
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index')
        ->middleware('permission:audit.view');
});
