<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Services\Aws\Ec2Service;
use App\Services\Aws\RdsService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServerController extends Controller
{
    public function index(Request $request, \App\Services\Aws\CostExplorerService $costService)
    {
        $workspace = app('activeWorkspace');

        $ec2Instances = $workspace->ec2Instances()
            ->orderByRaw("FIELD(state, 'running', 'pending', 'stopping', 'stopped') ASC")
            ->get();

        $rdsInstances = $workspace->rdsInstances()
            ->orderByRaw("FIELD(status, 'available', 'starting', 'stopping', 'stopped') ASC")
            ->get();

        $monthlyCost = $costService->getMonthlyCost($workspace);

        return Inertia::render('Dashboard/Servers/Index', [
            'ec2Instances' => $ec2Instances,
            'rdsInstances' => $rdsInstances,
            'monthlyCost' => $monthlyCost,
            'lastSyncedAt' => $workspace->last_synced_at?->diffForHumans() ?? 'Never',
            'hasAwsCredentials' => $workspace->hasAwsCredentials(),
        ]);
    }

    public function start(Request $request, string $instanceId)
    {
        $workspace = app('activeWorkspace');
        $type = $request->input('type', 'ec2');

        if (!$workspace->hasAwsCredentials()) {
            return back()->with('error', 'AWS Credentials are not configured. Please add them in Settings.');
        }

        ActivityLog::create([
            'workspace_id' => $workspace->id,
            'user_id' => $request->user()->id,
            'actor_type' => 'user',
            'action' => 'started',
            'resource_type' => strtoupper($type),
            'resource_id' => $instanceId,
            'resource_name' => $request->input('name', $instanceId),
            'ip_address' => $request->ip(),
        ]);

        try {
            if ($type === 'rds') {
                app(RdsService::class)->startInstance($workspace, $instanceId);
            } else {
                app(Ec2Service::class)->startInstance($workspace, $instanceId);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to start instance: ' . $e->getMessage());
        }

        return back()->with('success', "Starting {$instanceId}...");
    }

    public function stop(Request $request, string $instanceId)
    {
        $workspace = app('activeWorkspace');
        $type = $request->input('type', 'ec2');

        if (!$workspace->hasAwsCredentials()) {
            return back()->with('error', 'AWS Credentials are not configured. Please add them in Settings.');
        }

        ActivityLog::create([
            'workspace_id' => $workspace->id,
            'user_id' => $request->user()->id,
            'actor_type' => 'user',
            'action' => 'stopped',
            'resource_type' => strtoupper($type),
            'resource_id' => $instanceId,
            'resource_name' => $request->input('name', $instanceId),
            'ip_address' => $request->ip(),
        ]);

        try {
            if ($type === 'rds') {
                app(RdsService::class)->stopInstance($workspace, $instanceId);
            } else {
                app(Ec2Service::class)->stopInstance($workspace, $instanceId);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to stop instance: ' . $e->getMessage());
        }

        return back()->with('success', "Stopping {$instanceId}...");
    }

    public function refresh(Request $request, string $instanceId)
    {
        $workspace = app('activeWorkspace');
        $type = $request->input('type', 'ec2');

        if (!$workspace->hasAwsCredentials()) {
            return back()->with('error', 'AWS Credentials are not configured.');
        }

        try {
            if ($type === 'rds') {
                $status = app(RdsService::class)->syncSingleInstance($workspace, $instanceId);
            } else {
                $status = app(Ec2Service::class)->syncSingleInstance($workspace, $instanceId);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to refresh: ' . $e->getMessage());
        }

        return back()->with('success', "Updated status of {$instanceId} to " . strtoupper($status));
    }

    public function sync(Request $request)
    {
        $workspace = app('activeWorkspace');

        if (!$workspace->hasAwsCredentials()) {
            return back()->with('error', 'Cannot sync: AWS Credentials are not configured in Settings.');
        }

        try {
            $ec2Count = app(Ec2Service::class)->syncInstances($workspace);
            $rdsCount = app(RdsService::class)->syncInstances($workspace);

            $workspace->update(['last_synced_at' => now()]);

            ActivityLog::create([
                'workspace_id' => $workspace->id,
                'user_id' => $request->user()->id,
                'actor_type' => 'user',
                'action' => 'created',
                'resource_type' => 'Sync',
                'resource_id' => null,
                'resource_name' => 'Manual Sync',
                'metadata' => ['ec2_count' => $ec2Count, 'rds_count' => $rdsCount],
                'ip_address' => $request->ip(),
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'AWS Sync failed: ' . $e->getMessage());
        }

        return back()->with('success', "Synced {$ec2Count} EC2 and {$rdsCount} RDS instances.");
    }
}
