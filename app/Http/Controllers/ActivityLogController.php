<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $workspace = app('activeWorkspace');

        $query = $workspace->activityLogs()->with('user:id,name,email')->latest();

        if ($request->has('actor_type') && $request->actor_type !== 'all') {
            $query->where('actor_type', $request->actor_type);
        }

        $logs = $query->paginate(50);

        $logs->getCollection()->transform(function ($log) {
            return [
                'id' => $log->id,
                'time' => $log->created_at->format('H:i'),
                'date' => $log->created_at->format('M d, Y'),
                'actor_type' => $log->actor_type,
                'actor_name' => $log->user?->name ?? ucfirst($log->actor_type),
                'actor_initials' => $log->user?->initials,
                'action' => $log->action,
                'resource_type' => $log->resource_type,
                'resource_id' => $log->resource_id,
                'resource_name' => $log->resource_name,
                'metadata' => $log->metadata,
                'ip_address' => $log->ip_address,
            ];
        });

        return Inertia::render('Dashboard/Activity/Index', [
            'logs' => $logs,
        ]);
    }
}
