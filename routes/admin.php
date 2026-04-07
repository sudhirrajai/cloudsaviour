<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WorkspaceController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\WaitlistController;

// Admin Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

// User Management
Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
Route::get('/users/{user}', [UserController::class, 'show'])->name('admin.users.show');
Route::post('/users/{user}/toggle-admin', [UserController::class, 'toggleAdmin'])->name('admin.users.toggle-admin');
Route::post('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('admin.users.toggle-active');
Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');

// Workspace & Subscription Management
Route::get('/workspaces', [WorkspaceController::class, 'index'])->name('admin.workspaces.index');
Route::put('/workspaces/{workspace}/plan', [WorkspaceController::class, 'updatePlan'])->name('admin.workspaces.update-plan');
Route::post('/workspaces/{workspace}/toggle-active', [WorkspaceController::class, 'toggleActive'])->name('admin.workspaces.toggle-active');

// Plan Management
Route::resource('plans', PlanController::class);

// Waitlist Management
Route::get('/waitlists', [WaitlistController::class, 'index'])->name('admin.waitlists.index');
Route::post('/waitlists/config', [WaitlistController::class, 'updateConfig'])->name('admin.waitlists.update-config');
Route::post('/waitlists/{waitlist}/account', [WaitlistController::class, 'createAccount'])->name('admin.waitlists.create-account');

