<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\IdleResource;
use App\Services\Aws\IdleScannerService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IdleScannerController extends Controller
{
    public function index(Request $request)
    {
        $workspace = app('activeWorkspace');

        $idleResources = $workspace->idleResources()
            ->whereNull('resolved_at')
            ->orderByDesc('estimated_monthly_cost')
            ->get();

        $stats = [
            'total' => $idleResources->where('is_ignored', false)->count(),
            'totalSavings' => $idleResources->where('is_ignored', false)->sum('estimated_monthly_cost'),
            'ignored' => $idleResources->where('is_ignored', true)->count(),
        ];

        return Inertia::render('Dashboard/IdleScanner/Index', [
            'idleResources' => $idleResources,
            'stats' => $stats,
        ]);
    }

    public function resolve(Request $request, int $id)
    {
        $workspace = app('activeWorkspace');
        $resource = IdleResource::where('workspace_id', $workspace->id)->findOrFail($id);

        try {
            // Attempt to delete from AWS
            app(IdleScannerService::class)->deleteResource($workspace, $resource);

            // If successful, mark as resolved in DB
            $resource->update(['resolved_at' => now()]);

            ActivityLog::create([
                'workspace_id' => $workspace->id,
                'user_id' => $request->user()->id,
                'actor_type' => 'user',
                'action' => 'deleted',
                'resource_type' => 'Idle',
                'resource_id' => $resource->resource_id,
                'resource_name' => $resource->resource_name,
                'ip_address' => $request->ip(),
            ]);

            return back()->with('success', "Resource {$resource->resource_id} has been permanently deleted from AWS.");
        } catch (\Exception $e) {
            report($e);
            return back()->with('error', $this->formatAwsError($e));
        }
    }

    private function formatAwsError(\Exception $e): string
    {
        $message = $e->getMessage();

        if (str_contains($message, 'UnauthorizedOperation') || str_contains($message, 'AccessDenied')) {
            // Extract the missing action from the AWS message
            preg_match('/perform: ([\w:]+)/', $message, $matches);
            $action = $matches[1] ?? 'this operation';
            
            return "Permission Denied: Your AWS IAM user is missing the '{$action}' permission. Please update your IAM policy in the AWS Console.";
        }

        if (str_contains($message, 'Request expired')) {
            return "Session Expired: Your local time may be out of sync, or your AWS credentials have expired.";
        }

        // Return a cleaner version of the generic error
        return "AWS Error: " . (explode(';', $message)[0] ?? $message);
    }

    public function ignore(Request $request, int $id)
    {
        $workspace = app('activeWorkspace');
        $resource = IdleResource::where('workspace_id', $workspace->id)->findOrFail($id);

        $resource->update(['is_ignored' => true, 'ignored_at' => now()]);

        return back()->with('success', 'Resource ignored.');
    }

    public function scan(Request $request)
    {
        $workspace = app('activeWorkspace');
        $count = app(IdleScannerService::class)->scan($workspace);

        ActivityLog::create([
            'workspace_id' => $workspace->id,
            'user_id' => $request->user()->id,
            'actor_type' => 'user',
            'action' => 'created',
            'resource_type' => 'Scan',
            'resource_id' => null,
            'resource_name' => 'Idle Resource Scan',
            'metadata' => ['detected' => $count],
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', "Scan complete. {$count} idle resources detected.");
    }
}
