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

    public function apply(Request $request, int $id)
    {
        $workspace = app('activeWorkspace');
        $rec = AiRecommendation::where('workspace_id', $workspace->id)->findOrFail($id);

        $rec->update(['status' => 'applied', 'applied_at' => now()]);

        ActivityLog::create([
            'workspace_id' => $workspace->id,
            'user_id' => $request->user()->id,
            'actor_type' => 'user',
            'action' => 'applied',
            'resource_type' => $rec->resource_type,
            'resource_id' => $rec->resource_id,
            'resource_name' => $rec->title,
            'ip_address' => $request->ip(),
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
