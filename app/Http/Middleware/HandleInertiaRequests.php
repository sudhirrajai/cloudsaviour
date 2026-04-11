<?php

namespace App\Http\Middleware;

use App\Models\Workspace;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();
        $activeWorkspace = null;
        $workspaces = [];

        if ($user) {
            $workspaces = $user->workspaces()->select('workspaces.id', 'workspaces.name', 'workspaces.slug', 'workspaces.plan')->get();
            $activeWorkspaceId = session('active_workspace_id');
            if ($activeWorkspaceId) {
                $activeWorkspace = $workspaces->firstWhere('id', $activeWorkspaceId);
            }
        }

        $notifications = [];
        if ($activeWorkspace) {
            $notifications = \App\Models\ActivityLog::with('user:id,name,email')
                ->where('workspace_id', $activeWorkspace->id)
                ->latest()
                ->take(10)
                ->get()
                ->map(function ($log) {
                    $actor = $log->actor_type === 'user' ? ($log->user ? $log->user->name : 'A user') : 'System';
                    $description = "{$actor} performed {$log->action}";
                    
                    if ($log->actor_type === 'user') {
                        if ($log->action === 'started' || $log->action === 'stopped') {
                            $description = "{$actor} {$log->action} {$log->resource_type} {$log->resource_id}";
                        } elseif ($log->action === 'joined') {
                            $description = "{$actor} joined the workspace";
                        } elseif ($log->action === 'invited') {
                            $email = $log->metadata['email'] ?? 'a user';
                            $description = "{$actor} invited {$email} to the workspace";
                        } else if ($log->resource_type) {
                            $description = "{$actor} {$log->action} on {$log->resource_type}";
                        }
                    } else {
                        if ($log->action === 'synced') {
                            $description = "AWS synchronization completed successfully.";
                        } elseif ($log->action === 'scanned') {
                            $description = "Idle scanner completed its run.";
                        } elseif ($log->action === 'executed') {
                            $description = "Scheduled action executed successfully.";
                        } else if ($log->resource_type) {
                            $description = "System performed {$log->action} on {$log->resource_type}";
                        }
                    }

                    if (isset($log->metadata['message'])) {
                        $description = $log->metadata['message'];
                    }

                    // Determine icon and color based on action/actor
                    $icon = 'notifications';
                    $colorClass = 'text-slate-400 bg-slate-400/20';
                    if ($log->action === 'synced' || $log->action === 'scanned') {
                        $icon = 'sync';
                        $colorClass = 'text-tertiary bg-tertiary/20';
                    } elseif ($log->action === 'started' || $log->action === 'executed') {
                        $icon = 'play_circle';
                        $colorClass = 'text-success bg-success/20';
                    } elseif ($log->action === 'stopped') {
                        $icon = 'stop_circle';
                        $colorClass = 'text-warning bg-warning/20';
                    } elseif (in_array($log->action, ['joined', 'invited'])) {
                        $icon = 'person_add';
                        $colorClass = 'text-primary bg-primary/20';
                    }

                    return [
                        'id' => $log->id,
                        'description' => $description,
                        'time' => $log->created_at->diffForHumans(),
                        'icon' => $icon,
                        'colorClass' => $colorClass,
                    ];
                });
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'initials' => $user->initials,
                    'is_admin' => (bool)$user->is_admin,
                    'role' => $activeWorkspace ? $user->roleIn($activeWorkspace) : null,
                    'owns_any_workspace' => $user->ownedWorkspaces()->exists(),
                ] : null,
            ],
            'activeWorkspace' => $activeWorkspace,
            'workspaces' => $workspaces,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'notifications' => $notifications,
        ];
    }
}
