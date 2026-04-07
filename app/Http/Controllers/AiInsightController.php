<?php

namespace App\Http\Controllers;

use App\Models\AiRecommendation;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

use App\Services\Aws\Ec2Service;
use App\Services\Aws\RdsService;

class AiInsightController extends Controller
{
    public function __construct(
        private Ec2Service $ec2Service,
        private RdsService $rdsService
    ) {}

    public function index(Request $request)
    {
        $workspace = app('activeWorkspace');

        $recommendations = $workspace->aiRecommendations()
            ->orderByRaw("FIELD(status, 'pending', 'applied', 'dismissed') ASC")
            ->orderByDesc('estimated_monthly_saving')
            ->get();

        return Inertia::render('Dashboard/AiInsights/Index', [
            'recommendations' => $recommendations,
        ]);
    }

    public function refresh(Request $request)
    {
        $workspace = app('activeWorkspace');

        // Dispatch the scan job which now also runs the AI engine
        \App\Jobs\ScanIdleResourcesJob::dispatch($workspace->id);

        ActivityLog::create([
            'workspace_id' => $workspace->id,
            'user_id' => $request->user()->id,
            'actor_type' => 'ai',
            'action' => 'created',
            'resource_type' => 'Analysis',
            'resource_id' => null,
            'resource_name' => 'AI Infrastructure Analysis',
            'ip_address' => $request->ip(),
            'metadata' => ['triggered_by' => $request->user()->name]
        ]);

        return back()->with('success', 'AI analysis has been triggered. Your insights will refresh shortly.');
    }

    public function apply(Request $request, int $id)
    {
        $workspace = app('activeWorkspace');
        $rec = AiRecommendation::where('workspace_id', $workspace->id)->findOrFail($id);

        try {
            // Identify and execute the actual AWS action
            switch ($rec->resource_type) {
                case 'ec2_zombie':
                case 'ec2_instance':
                case 'ec2':
                    $this->ec2Service->terminateInstance($workspace, $rec->resource_id);
                    break;

                case 'rds':
                case 'rds_instance':
                    $this->rdsService->deleteInstance($workspace, $rec->resource_id);
                    break;

                case 'ebs_volume':
                    $this->ec2Service->deleteVolume($workspace, $rec->resource_id);
                    break;

                case 'elastic_ip':
                    $this->ec2Service->releaseAddress($workspace, $rec->resource_id);
                    break;

                default:
                    // If it's a "schedule" or "resize", we might need separate logic,
                    // but for now, we mark as applied for general "fix" clicks.
                    break;
            }

            // Update status only if successful
            $rec->update(['status' => 'applied', 'applied_at' => now()]);

            ActivityLog::create([
                'workspace_id' => $workspace->id,
                'user_id' => $request->user()->id,
                'actor_type' => 'ai',
                'action' => 'applied',
                'resource_type' => $rec->resource_type,
                'resource_id' => $rec->resource_id,
                'resource_name' => $rec->title,
                'ip_address' => $request->ip(),
                'metadata' => ['applied_by' => $request->user()->name]
            ]);

            return back()->with('success', "Action applied successfully: {$rec->title}");
        } catch (\Exception $e) {
            \Log::error("AI Action Failed on recommendation {$id}: " . $e->getMessage());
            return back()->with('error', "Could not apply action: " . $e->getMessage());
        }
    }

    public function dismiss(Request $request, int $id)
    {
        $workspace = app('activeWorkspace');
        $rec = AiRecommendation::where('workspace_id', $workspace->id)->findOrFail($id);

        $rec->update(['status' => 'dismissed']);

        return back()->with('success', 'Recommendation dismissed.');
    }
}
