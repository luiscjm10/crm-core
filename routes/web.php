<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('companies', CompanyController::class);

    Route::middleware('role:super-admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
        Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
        Route::get('permissions', [\App\Http\Controllers\Admin\PermissionController::class, 'index'])->name('permissions.index');
        Route::get('tasks', [\App\Http\Controllers\Admin\TaskController::class, 'index'])->name('tasks.index');
    });

    Route::middleware('role:super-admin')->prefix('admin/companies/{company}')->name('admin.companies.tasks.')->scopeBindings()->group(function () {
        Route::get('tasks', [\App\Http\Controllers\Admin\TaskController::class, 'index'])->name('index');
        Route::get('tasks/create', [\App\Http\Controllers\Admin\TaskController::class, 'create'])->name('create');
        Route::post('tasks', [\App\Http\Controllers\Admin\TaskController::class, 'store'])->name('store');
        Route::get('tasks/{task}', [\App\Http\Controllers\Admin\TaskController::class, 'show'])->name('show');
        Route::get('tasks/{task}/edit', [\App\Http\Controllers\Admin\TaskController::class, 'edit'])->name('edit');
        Route::put('tasks/{task}', [\App\Http\Controllers\Admin\TaskController::class, 'update'])->name('update');
        Route::patch('tasks/{task}/complete', [\App\Http\Controllers\Admin\TaskController::class, 'complete'])->name('complete');
        Route::patch('tasks/{task}/update-status', [\App\Http\Controllers\Admin\TaskController::class, 'updateStatus'])->name('update-status');
        Route::delete('tasks/{task}', [\App\Http\Controllers\Admin\TaskController::class, 'destroy'])->name('destroy');
    });
});

require __DIR__ . '/auth.php';
