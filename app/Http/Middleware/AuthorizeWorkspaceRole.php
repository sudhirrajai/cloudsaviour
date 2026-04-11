<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeWorkspaceRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        $workspace = app('activeWorkspace');

        if (!$user || !$workspace) {
            abort(403, 'Unauthorized. No active workspace found.');
        }

        // Get the user's role in the active workspace
        $member = $workspace->members()->where('user_id', $user->id)->first();
        
        if (!$member || !in_array($member->pivot->role, $roles)) {
            abort(403, 'You do not have the required permissions (' . implode(', ', $roles) . ') to perform this action.');
        }

        return $next($request);
    }
}
