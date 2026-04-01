<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->input('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => (bool)$user->is_admin,
                'is_active' => (bool)$user->is_active,
                'created_at' => $user->created_at->format('M d, Y'),
                'workspaces_count' => $user->workspaces()->count(),
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search']),
        ]);
    }

    public function show(User $user)
    {
        return Inertia::render('Admin/Users/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => (bool)$user->is_admin,
                'is_active' => (bool)$user->is_active,
                'created_at' => $user->created_at->format('M d, Y'),
                'workspaces' => $user->workspaces()->withCount(['ec2Instances', 'rdsInstances'])->get(),
                'activity_logs' => $user->activityLogs()->latest()->take(10)->get(),
            ]
        ]);
    }

    public function toggleAdmin(User $user)
    {
        if (Auth::id() === $user->id) {
            return back()->with('error', 'You cannot change your own admin status.');
        }

        $user->update(['is_admin' => !$user->is_admin]);
        return back()->with('success', 'User admin status updated.');
    }

    public function toggleActive(User $user)
    {
        if (Auth::id() === $user->id) {
            return back()->with('error', 'You cannot deactivate yourself.');
        }

        $user->update(['is_active' => !$user->is_active]);
        
        // If deactivated, we might want to also deactivate their primary workspace
        if (!$user->is_active) {
            $user->ownedWorkspaces()->update(['is_active' => false]);
        }

        return back()->with('success', 'User activity status updated.');
    }

    public function destroy(User $user)
    {
        if (Auth::id() === $user->id) {
            return back()->with('error', 'You cannot terminate yourself.');
        }

        // The user asked for "deactivation" in the comment, but "terminate" usually means delete.
        // We will follow the deactivation path for safety if they just want them gone from view.
        // But since we already have toggleActive, destroy will be a hard-deactivation plus workspace shutdown.
        $user->update(['is_active' => false]);
        $user->ownedWorkspaces()->update(['is_active' => false]);

        return redirect()->route('admin.users.index')->with('success', 'User terminated and workspaces suspended.');
    }
}
