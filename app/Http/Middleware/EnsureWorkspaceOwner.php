<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Workspace;
use Symfony\Component\HttpFoundation\Response;

class EnsureWorkspaceOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $workspaceId = session('active_workspace_id');

        if (!$workspaceId) {
            abort(403, 'No active workspace.');
        }

        $workspace = Workspace::find($workspaceId);

        if (!$workspace || $workspace->owner_id !== $user->id) {
            abort(403, 'You must be the workspace owner to perform this action.');
        }

        return $next($request);
    }
}
