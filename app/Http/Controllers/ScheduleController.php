<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $workspace = app('activeWorkspace');

        $schedules = $workspace->schedules()->orderByDesc('is_active')->orderBy('time_of_day')->get();

        return Inertia::render('Dashboard/Schedules/Index', [
            'schedules' => $schedules,
        ]);
    }

    public function store(Request $request)
    {
        $workspace = app('activeWorkspace');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'resource_type' => ['required', 'in:ec2,rds'],
            'resource_id' => ['required', 'string'],
            'action' => ['required', 'in:start,stop'],
            'days_of_week' => ['required', 'array'],
            'days_of_week.*' => ['integer', 'between:1,7'],
            'time_of_day' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'timezone' => ['required', 'string'],
        ]);

        $schedule = Schedule::create([
            'workspace_id' => $workspace->id,
            ...$validated,
            'is_active' => true,
        ]);

        ActivityLog::create([
            'workspace_id' => $workspace->id,
            'user_id' => $request->user()->id,
            'actor_type' => 'user',
            'action' => 'created',
            'resource_type' => 'Schedule',
            'resource_id' => (string) $schedule->id,
            'resource_name' => $schedule->name,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Schedule created.');
    }

    public function update(Request $request, int $id)
    {
        $workspace = app('activeWorkspace');
        $schedule = Schedule::where('workspace_id', $workspace->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'resource_type' => ['required', 'in:ec2,rds'],
            'resource_id' => ['required', 'string'],
            'action' => ['required', 'in:start,stop'],
            'days_of_week' => ['required', 'array'],
            'time_of_day' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'timezone' => ['required', 'string'],
        ]);

        $schedule->update($validated);

        return back()->with('success', 'Schedule updated.');
    }

    public function destroy(Request $request, int $id)
    {
        $workspace = app('activeWorkspace');
        $schedule = Schedule::where('workspace_id', $workspace->id)->findOrFail($id);

        ActivityLog::create([
            'workspace_id' => $workspace->id,
            'user_id' => $request->user()->id,
            'actor_type' => 'user',
            'action' => 'deleted',
            'resource_type' => 'Schedule',
            'resource_id' => (string) $schedule->id,
            'resource_name' => $schedule->name,
            'ip_address' => $request->ip(),
        ]);

        $schedule->delete();

        return back()->with('success', 'Schedule deleted.');
    }

    public function toggle(Request $request, int $id)
    {
        $workspace = app('activeWorkspace');
        $schedule = Schedule::where('workspace_id', $workspace->id)->findOrFail($id);

        $schedule->update(['is_active' => !$schedule->is_active]);

        return back()->with('success', $schedule->is_active ? 'Schedule activated.' : 'Schedule paused.');
    }
}
