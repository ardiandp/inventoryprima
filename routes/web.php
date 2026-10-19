<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ItemCategoryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemUnitConversionController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WarehouseLocationController;
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

    // --- PHASE 3: Master Data (prefix /master) ---
    Route::prefix('master')->name('master.')->group(function () {
        // Barang
        Route::middleware('permission:master.view')->group(function () {
            Route::get('/items', [ItemController::class, 'index'])->name('items.index');
        });
        Route::middleware('permission:master.create')->group(function () {
            Route::get('/items/create', [ItemController::class, 'create'])->name('items.create');
            Route::post('/items', [ItemController::class, 'store'])->name('items.store');
        });
        Route::middleware('permission:master.edit')->group(function () {
            Route::get('/items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
            Route::put('/items/{item}', [ItemController::class, 'update'])->name('items.update');
            // Konversi satuan (dikelola dari form edit barang)
            Route::post('/items/{item}/conversions', [ItemUnitConversionController::class, 'store'])
                ->name('items.conversions.store');
            Route::delete('/items/{item}/conversions/{conversion}', [ItemUnitConversionController::class, 'destroy'])
                ->name('items.conversions.destroy');
        });
        Route::middleware('permission:master.delete')->group(function () {
            Route::delete('/items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');
        });

        // Kategori barang
        Route::middleware('permission:master.view')->group(function () {
            Route::get('/categories', [ItemCategoryController::class, 'index'])->name('categories.index');
        });
        Route::middleware('permission:master.create')->group(function () {
            Route::get('/categories/create', [ItemCategoryController::class, 'create'])->name('categories.create');
            Route::post('/categories', [ItemCategoryController::class, 'store'])->name('categories.store');
        });
        Route::middleware('permission:master.edit')->group(function () {
            Route::get('/categories/{category}/edit', [ItemCategoryController::class, 'edit'])->name('categories.edit');
            Route::put('/categories/{category}', [ItemCategoryController::class, 'update'])->name('categories.update');
        });
        Route::middleware('permission:master.delete')->group(function () {
            Route::delete('/categories/{category}', [ItemCategoryController::class, 'destroy'])->name('categories.destroy');
        });

        // Satuan
        Route::middleware('permission:master.view')->group(function () {
            Route::get('/units', [UnitController::class, 'index'])->name('units.index');
        });
        Route::middleware('permission:master.create')->group(function () {
            Route::get('/units/create', [UnitController::class, 'create'])->name('units.create');
            Route::post('/units', [UnitController::class, 'store'])->name('units.store');
        });
        Route::middleware('permission:master.edit')->group(function () {
            Route::get('/units/{unit}/edit', [UnitController::class, 'edit'])->name('units.edit');
            Route::put('/units/{unit}', [UnitController::class, 'update'])->name('units.update');
        });
        Route::middleware('permission:master.delete')->group(function () {
            Route::delete('/units/{unit}', [UnitController::class, 'destroy'])->name('units.destroy');
        });

        // Supplier
        Route::middleware('permission:master.view')->group(function () {
            Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
        });
        Route::middleware('permission:master.create')->group(function () {
            Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
            Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
        });
        Route::middleware('permission:master.edit')->group(function () {
            Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
            Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
        });
        Route::middleware('permission:master.delete')->group(function () {
            Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
        });

        // Gudang
        Route::middleware('permission:master.view')->group(function () {
            Route::get('/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
        });
        Route::middleware('permission:master.create')->group(function () {
            Route::get('/warehouses/create', [WarehouseController::class, 'create'])->name('warehouses.create');
            Route::post('/warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
        });
        Route::middleware('permission:master.edit')->group(function () {
            Route::get('/warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('warehouses.edit');
            Route::put('/warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update');
        });
        Route::middleware('permission:master.delete')->group(function () {
            Route::delete('/warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');
        });

        // Lokasi gudang
        Route::middleware('permission:master.view')->group(function () {
            Route::get('/locations', [WarehouseLocationController::class, 'index'])->name('locations.index');
        });
        Route::middleware('permission:master.create')->group(function () {
            Route::get('/locations/create', [WarehouseLocationController::class, 'create'])->name('locations.create');
            Route::post('/locations', [WarehouseLocationController::class, 'store'])->name('locations.store');
        });
        Route::middleware('permission:master.edit')->group(function () {
            Route::get('/locations/{location}/edit', [WarehouseLocationController::class, 'edit'])->name('locations.edit');
            Route::put('/locations/{location}', [WarehouseLocationController::class, 'update'])->name('locations.update');
        });
        Route::middleware('permission:master.delete')->group(function () {
            Route::delete('/locations/{location}', [WarehouseLocationController::class, 'destroy'])->name('locations.destroy');
        });

        // Proyek (opsional)
        Route::middleware('permission:master.view')->group(function () {
            Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
        });
        Route::middleware('permission:master.create')->group(function () {
            Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
            Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
        });
        Route::middleware('permission:master.edit')->group(function () {
            Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
            Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
        });
        Route::middleware('permission:master.delete')->group(function () {
            Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
        });
    });
});
