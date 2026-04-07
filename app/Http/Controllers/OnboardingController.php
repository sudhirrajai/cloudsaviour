<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Workspace;

class OnboardingController extends Controller
{
    public function index(Request $request)
    {
        $workspace = $request->workspace ?? app('activeWorkspace');
        
        // If they already have credentials, they technically don't need onboarding, but let them see it if they manually land here.
        
        return Inertia::render('Dashboard/Onboarding/Index', [
            'hasCredentials' => $workspace && $workspace->hasAwsCredentials(),
        ]);
    }

    public function store(Request $request)
    {
        $workspace = $request->workspace ?? app('activeWorkspace');

        if (!$workspace) {
            return redirect('/dashboard/workspace/create');
        }

        // If skipped
        if ($request->has('skip') && $request->skip) {
            return redirect('/dashboard/servers')->with('start_tour', true);
        }

        $validated = $request->validate([
            'aws_access_key' => ['required', 'string'],
            'aws_secret_key' => ['required', 'string'],
            'aws_region' => ['required', 'string'],
        ]);

        $workspace->update([
            'aws_access_key' => $validated['aws_access_key'],
            'aws_secret_key' => $validated['aws_secret_key'],
            'aws_region' => $validated['aws_region'],
        ]);

        return redirect('/dashboard/servers')->with('success', 'AWS Credentials saved successfully!')->with('start_tour', true);
    }
}
