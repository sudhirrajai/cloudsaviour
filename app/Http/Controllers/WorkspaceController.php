<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class WorkspaceController extends Controller
{
    public function create(Request $request)
    {
        $user = $request->user();
        if ($user->workspaces()->count() > 0 && !$user->ownedWorkspaces()->exists()) {
            return redirect()->route('servers.index')->with('error', 'You do not have permission to create workspaces.');
        }
        
        return Inertia::render('Dashboard/Workspace/Create');
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if ($user->workspaces()->count() > 0 && !$user->ownedWorkspaces()->exists()) {
            abort(403, 'You do not have permission to create workspaces.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $workspace = Workspace::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . Str::random(6),
            'owner_id' => $user->id,
        ]);

        $workspace->members()->attach($user->id, [
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        session(['active_workspace_id' => $workspace->id]);

        return redirect('/dashboard/onboarding');
    }

    public function switch(Request $request)
    {
        $request->validate([
            'workspace_id' => ['required', 'integer'],
        ]);

        $user = $request->user();
        $workspaceId = $request->workspace_id;

        if ($user->workspaces()->count() > 0 && !$user->ownedWorkspaces()->exists()) {
            abort(403, 'You are bound to your current workspace and cannot switch.');
        }

        // Verify membership
        if (!$user->workspaces()->where('workspaces.id', $workspaceId)->exists()) {
            abort(403, 'You are not a member of this workspace.');
        }

        session(['active_workspace_id' => $workspaceId]);

        return redirect()->back();
    }
}
