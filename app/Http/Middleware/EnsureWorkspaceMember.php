<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Workspace;
use Symfony\Component\HttpFoundation\Response;

class EnsureWorkspaceMember
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect('/login');
        }

        $workspaceId = session('active_workspace_id');

        // If no active workspace, try to set the first one
        if (!$workspaceId) {
            $firstWorkspace = $user->workspaces()->first();
            if ($firstWorkspace) {
                session(['active_workspace_id' => $firstWorkspace->id]);
                $workspaceId = $firstWorkspace->id;
            } else {
                // User has no workspaces — redirect to create one
                if ($request->url() !== url('/dashboard/workspace/create')) {
                    return redirect('/dashboard/workspace/create');
                }
                return $next($request);
            }
        }

        // Verify membership
        $workspace = Workspace::find($workspaceId);
        if (!$workspace || !$user->workspaces()->where('workspace_id', $workspaceId)->exists()) {
            session()->forget('active_workspace_id');
            return redirect('/dashboard/servers');
        }

        // Share workspace with request
        $request->merge(['workspace' => $workspace]);
        app()->instance('activeWorkspace', $workspace);

        return $next($request);
    }
}
