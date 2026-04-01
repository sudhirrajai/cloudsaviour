<?php

namespace App\Http\Controllers;

use App\Models\AiRecommendation;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AiInsightController extends Controller
{
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

        $rec->update(['status' => 'applied', 'applied_at' => now()]);

        ActivityLog::create([
            'workspace_id' => $workspace->id,
            'user_id' => $request->user()->id,
            'actor_type' => 'ai', // Tagged as AI for the activity filter
            'action' => 'applied',
            'resource_type' => $rec->resource_type,
            'resource_id' => $rec->resource_id,
            'resource_name' => $rec->title,
            'ip_address' => $request->ip(),
            'metadata' => ['applied_by' => $request->user()->name]
        ]);

        return back()->with('success', 'Recommendation applied.');
    }

    public function dismiss(Request $request, int $id)
    {
        $workspace = app('activeWorkspace');
        $rec = AiRecommendation::where('workspace_id', $workspace->id)->findOrFail($id);

        $rec->update(['status' => 'dismissed']);

        return back()->with('success', 'Recommendation dismissed.');
    }
}
