<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Security\PermissionController;
use App\Http\Controllers\Security\RoleController;
use App\Http\Controllers\Security\UserController;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::prefix('seguridad')->name('security.')->group(function () {
        Route::middleware('permission:roles')->group(function () {
            Route::resource('roles', RoleController::class)
                ->parameters(['roles' => 'role'])
                ->only(['index', 'store', 'update', 'destroy']);
        });

        Route::middleware('permission:permisos')->group(function () {
            Route::resource('permisos', PermissionController::class)
                ->parameters(['permisos' => 'permission'])
                ->only(['index', 'store', 'update', 'destroy']);
        });

        Route::middleware('permission:usuarios')->group(function () {
            Route::resource('usuarios', UserController::class)
                ->parameters(['usuarios' => 'user'])
                ->only(['index', 'store', 'update', 'destroy']);
        });
    });

    Route::middleware('permission:clientes')->group(function () {
        Route::resource('clientes', ClientController::class)
            ->parameters(['clientes' => 'client'])
            ->only(['index', 'store', 'update', 'destroy']);
    });

    Route::middleware('permission:productos')->group(function () {
        Route::resource('productos', ProductController::class)
            ->parameters(['productos' => 'product'])
            ->only(['index', 'store', 'update', 'destroy']);
    });

    Route::middleware('permission:pedidos')->group(function () {
        Route::resource('pedidos', OrderController::class)
            ->parameters(['pedidos' => 'order'])
            ->only(['index', 'store', 'update', 'destroy']);
    });

    Route::middleware('permission:seguimientos')->group(function () {
        Route::resource('seguimientos', TrackingController::class)
            ->parameters(['seguimientos' => 'tracking'])
            ->only(['index', 'store', 'update', 'destroy']);
    });
});

require __DIR__.'/settings.php';
