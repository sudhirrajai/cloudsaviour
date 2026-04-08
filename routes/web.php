<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\CostController;
use App\Http\Controllers\IdleScannerController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\AiInsightController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\WaitlistController;

use Inertia\Inertia;

// Landing page
// Route::get('/', function () {
//     return Inertia::render('Landing');
// });

Route::get('/', [WaitlistController::class, 'index'])->name('waitlist');

// Route::get('/waitlist', [WaitlistController::class, 'index'])->name('waitlist');
Route::post('/waitlist', [WaitlistController::class, 'store'])->name('waitlist.store');

// Auth (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Dashboard — requires auth + workspace membership
Route::middleware(['auth', 'workspace'])->prefix('dashboard')->group(function () {
    Route::get('/', function () {
        return redirect()->route('servers.index');
    });

    // Workspace management
    Route::get('/workspace/create', [WorkspaceController::class, 'create']);
    Route::post('/workspace', [WorkspaceController::class, 'store']);
    Route::post('/workspace/switch', [WorkspaceController::class, 'switch']);

    // Onboarding
    Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding.index');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');

    // Servers
    Route::get('/servers', [ServerController::class, 'index'])->name('servers.index');
    Route::post('/servers/{instanceId}/start', [ServerController::class, 'start']);
    Route::post('/servers/{instanceId}/stop', [ServerController::class, 'stop']);
    Route::post('/servers/{instanceId}/refresh', [ServerController::class, 'refresh']);
    Route::post('/servers/sync', [ServerController::class, 'sync']);

    // Cost
    Route::get('/cost', [CostController::class, 'index'])->name('cost.index');

    // Idle Scanner
    Route::get('/idle', [IdleScannerController::class, 'index'])->name('idle.index');
    Route::post('/idle/{id}/resolve', [IdleScannerController::class, 'resolve']);
    Route::post('/idle/{id}/ignore', [IdleScannerController::class, 'ignore']);
    Route::post('/idle/scan', [IdleScannerController::class, 'scan']);

    // Schedules
    Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::post('/schedules', [ScheduleController::class, 'store']);
    Route::put('/schedules/{id}', [ScheduleController::class, 'update']);
    Route::delete('/schedules/{id}', [ScheduleController::class, 'destroy']);
    Route::patch('/schedules/{id}/toggle', [ScheduleController::class, 'toggle']);

    // AI Insights
    Route::get('/ai-insights', [AiInsightController::class, 'index'])->name('ai-insights.index');
    Route::post('/ai-insights/refresh', [AiInsightController::class, 'refresh'])->name('ai-insights.refresh');
    Route::post('/ai-insights/{id}/apply', [AiInsightController::class, 'apply']);
    Route::post('/ai-insights/{id}/dismiss', [AiInsightController::class, 'dismiss']);

    // Members
    Route::get('/members', [MemberController::class, 'index'])->name('members.index');
    Route::post('/members/invite', [MemberController::class, 'invite']);
    Route::put('/members/{userId}/role', [MemberController::class, 'updateRole']);
    Route::delete('/members/{userId}', [MemberController::class, 'remove']);

    // Activity
    Route::get('/activity', [ActivityLogController::class, 'index'])->name('activity.index');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/aws', [SettingsController::class, 'updateAws']);
    Route::put('/settings/workspace', [SettingsController::class, 'updateWorkspace']);
    Route::put('/settings/notifications', [SettingsController::class, 'updateNotifications']);
    Route::post('/settings/test-connection', [SettingsController::class, 'testConnection']);
    Route::delete('/settings/workspace', [SettingsController::class, 'deleteWorkspace']);
});
