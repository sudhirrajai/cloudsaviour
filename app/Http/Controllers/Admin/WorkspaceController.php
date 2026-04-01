<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Workspace;
use Inertia\Inertia;

class WorkspaceController extends Controller
{
    public function index(Request $request)
    {
        $workspaces = Workspace::query()
            ->with(['owner:id,name,email', 'plan:id,name,slug'])
            ->when($request->input('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn ($workspace) => [
                'id' => $workspace->id,
                'name' => $workspace->name,
                'slug' => $workspace->slug,
                'plan_name' => $workspace->plan?->name ?? 'Free',
                'plan_id' => $workspace->plan_id,
                'is_active' => (bool)$workspace->is_active,
                'owner_name' => $workspace->owner->name,
                'owner_email' => $workspace->owner->email,
                'created_at' => $workspace->created_at->format('M d, Y'),
            ]);

        return Inertia::render('Admin/Workspaces/Index', [
            'workspaces' => $workspaces,
            'plans' => \App\Models\Plan::where('is_active', true)->get(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function updatePlan(Request $request, Workspace $workspace)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $plan = \App\Models\Plan::findOrFail($request->plan_id);

        $workspace->update([
            'plan_id' => $plan->id,
            'plan' => $plan->slug, // Keep legacy column in sync
        ]);

        return back()->with('success', "Workspace plan updated to {$plan->name}.");
    }

    public function toggleActive(Workspace $workspace)
    {
        $workspace->update([
            'is_active' => !$workspace->is_active,
        ]);

        $status = $workspace->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Workspace {$workspace->name} has been {$status}.");
    }
}
