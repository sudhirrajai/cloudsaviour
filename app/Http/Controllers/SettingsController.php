<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Services\Aws\AwsClientFactory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $workspace = app('activeWorkspace');

        return Inertia::render('Dashboard/Settings/Index', [
            'workspace' => [
                'id' => $workspace->id,
                'name' => $workspace->name,
                'slug' => $workspace->slug,
                'plan' => $workspace->plan,
                'aws_region' => $workspace->aws_region,
                'aws_account_id' => $workspace->aws_account_id,
                'has_aws_credentials' => $workspace->hasAwsCredentials(),
                'created_at' => $workspace->created_at->format('F j, Y'),
                'notification_settings' => $workspace->notification_settings,
            ],
        ]);
    }

    public function updateAws(Request $request)
    {
        $workspace = app('activeWorkspace');

        $request->validate([
            'aws_access_key' => ['required', 'string'],
            'aws_secret_key' => ['required', 'string'],
            'aws_region' => ['required', 'string', 'max:30'],
            'aws_account_id' => ['nullable', 'string', 'max:30'],
        ]);

        $workspace->update([
            'aws_access_key' => $request->aws_access_key,
            'aws_secret_key' => $request->aws_secret_key,
            'aws_region' => $request->aws_region,
            'aws_account_id' => $request->aws_account_id,
        ]);

        ActivityLog::create([
            'workspace_id' => $workspace->id,
            'user_id' => $request->user()->id,
            'actor_type' => 'user',
            'action' => 'created',
            'resource_type' => 'Settings',
            'resource_name' => 'AWS credentials updated',
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'AWS credentials saved.');
    }

    public function updateWorkspace(Request $request)
    {
        $workspace = app('activeWorkspace');

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $workspace->update(['name' => $request->name]);

        return back()->with('success', 'Workspace updated.');
    }

    public function updateNotifications(Request $request)
    {
        $workspace = app('activeWorkspace');

        $request->validate([
            'prefs' => ['required', 'array'],
        ]);

        $workspace->update([
            'notification_settings' => $request->prefs,
        ]);

        return back()->with('success', 'Notification preferences saved.');
    }

    public function deleteWorkspace(Request $request)
    {
        $workspace = app('activeWorkspace');

        // Only owner can delete
        if ($workspace->owner_id !== $request->user()->id) {
            abort(403, 'Only the workspace owner can delete this workspace.');
        }

        $workspace->delete();
        session()->forget('active_workspace_id');

        // Try to switch to another workspace
        $nextWorkspace = $request->user()->workspaces()->first();
        if ($nextWorkspace) {
            session(['active_workspace_id' => $nextWorkspace->id]);
            return redirect('/dashboard/servers')->with('success', 'Workspace deleted.');
        }

        return redirect('/dashboard/workspace/create')->with('success', 'Workspace deleted. Please create a new one.');
    }

    public function testConnection(Request $request)
    {
        $workspace = app('activeWorkspace');

        if (!$workspace->hasAwsCredentials()) {
            return back()->with('error', 'No AWS credentials configured.');
        }

        try {
            $factory = app(AwsClientFactory::class);
            $client = $factory->ec2($workspace);
            $client->describeRegions(['RegionNames' => [$workspace->aws_region]]);
            return back()->with('success', 'AWS connection successful!');
        } catch (\Exception $e) {
            return back()->with('error', 'AWS connection failed: ' . $e->getMessage());
        }
    }
}
