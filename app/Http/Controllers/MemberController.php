<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\WorkspaceInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $workspace = app('activeWorkspace');

        $members = $workspace->members()
            ->select('users.id', 'users.name', 'users.email', 'users.avatar')
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'initials' => $user->initials,
                'role' => $user->pivot->role,
                'joined_at' => $user->pivot->joined_at,
            ]);

        $invitations = $workspace->invitations()
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->with('inviter:id,name')
            ->get();

        return Inertia::render('Dashboard/Members/Index', [
            'members' => $members,
            'invitations' => $invitations,
        ]);
    }

    public function invite(Request $request)
    {
        $workspace = app('activeWorkspace');

        $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'in:admin,developer,viewer'],
        ]);

        // Check if already a member
        $existingUser = User::where('email', $request->email)->first();
        if ($existingUser && $workspace->members()->where('user_id', $existingUser->id)->exists()) {
            return back()->withErrors(['email' => 'This user is already a member.']);
        }

        // Check if invitation already pending
        $existingInvite = $workspace->invitations()
            ->where('email', $request->email)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($existingInvite) {
            return back()->withErrors(['email' => 'An invitation is already pending for this email.']);
        }

        WorkspaceInvitation::create([
            'workspace_id' => $workspace->id,
            'email' => $request->email,
            'role' => $request->role,
            'token' => Str::random(64),
            'invited_by' => $request->user()->id,
            'expires_at' => now()->addDays(7),
        ]);

        ActivityLog::create([
            'workspace_id' => $workspace->id,
            'user_id' => $request->user()->id,
            'actor_type' => 'user',
            'action' => 'created',
            'resource_type' => 'Invitation',
            'resource_id' => $request->email,
            'resource_name' => "Invited {$request->email} as {$request->role}",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', "Invitation sent to {$request->email}.");
    }

    public function updateRole(Request $request, int $userId)
    {
        $workspace = app('activeWorkspace');

        $request->validate([
            'role' => ['required', 'in:admin,developer,viewer'],
        ]);

        // Cannot change owner role
        if ($workspace->owner_id === $userId) {
            return back()->withErrors(['role' => 'Cannot change the workspace owner\'s role.']);
        }

        $workspace->members()->updateExistingPivot($userId, [
            'role' => $request->role,
        ]);

        return back()->with('success', 'Role updated.');
    }

    public function remove(Request $request, int $userId)
    {
        $workspace = app('activeWorkspace');

        // Cannot remove owner
        if ($workspace->owner_id === $userId) {
            return back()->withErrors(['error' => 'Cannot remove the workspace owner.']);
        }

        // Cannot remove yourself
        if ($request->user()->id === $userId) {
            return back()->withErrors(['error' => 'You cannot remove yourself.']);
        }

        $user = User::findOrFail($userId);

        $workspace->members()->detach($userId);

        ActivityLog::create([
            'workspace_id' => $workspace->id,
            'user_id' => $request->user()->id,
            'actor_type' => 'user',
            'action' => 'deleted',
            'resource_type' => 'Member',
            'resource_id' => (string) $userId,
            'resource_name' => $user->name,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', "{$user->name} removed from workspace.");
    }
}
