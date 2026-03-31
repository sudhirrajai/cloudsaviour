<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class WorkspaceController extends Controller
{
    public function create()
    {
        return Inertia::render('Dashboard/Workspace/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $user = $request->user();

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

        return redirect('/dashboard/servers');
    }

    public function switch(Request $request)
    {
        $request->validate([
            'workspace_id' => ['required', 'integer'],
        ]);

        $user = $request->user();
        $workspaceId = $request->workspace_id;

        // Verify membership
        if (!$user->workspaces()->where('workspace_id', $workspaceId)->exists()) {
            abort(403, 'You are not a member of this workspace.');
        }

        session(['active_workspace_id' => $workspaceId]);

        return redirect()->back();
    }
}
