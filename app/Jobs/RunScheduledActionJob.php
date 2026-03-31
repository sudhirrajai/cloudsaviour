<?php

namespace App\Jobs;

use App\Models\ActivityLog;
use App\Models\Schedule;
use App\Services\Aws\Ec2Service;
use App\Services\Aws\RdsService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunScheduledActionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(Ec2Service $ec2Service, RdsService $rdsService): void
    {
        $now = Carbon::now();

        Schedule::where('is_active', true)
            ->with('workspace')
            ->each(function (Schedule $schedule) use ($now, $ec2Service, $rdsService) {
                try {
                    // Check if current day is in schedule
                    $currentDow = $now->copy()->setTimezone($schedule->timezone)->dayOfWeekIso; // 1=Mon, 7=Sun
                    if (!in_array($currentDow, $schedule->days_of_week)) {
                        return;
                    }

                    // Check if current time matches (within 1-minute window)
                    $currentTime = $now->copy()->setTimezone($schedule->timezone)->format('H:i');
                    if ($currentTime !== $schedule->time_of_day) {
                        return;
                    }

                    // Prevent re-runs within same minute
                    if ($schedule->last_run_at && $schedule->last_run_at->diffInMinutes($now) < 1) {
                        return;
                    }

                    // Execute action
                    $workspace = $schedule->workspace;
                    if (!$workspace || !$workspace->hasAwsCredentials()) {
                        return;
                    }

                    if ($schedule->resource_type === 'ec2') {
                        if ($schedule->action === 'start') {
                            $ec2Service->startInstance($workspace, $schedule->resource_id);
                        } else {
                            $ec2Service->stopInstance($workspace, $schedule->resource_id);
                        }
                    } elseif ($schedule->resource_type === 'rds') {
                        if ($schedule->action === 'start') {
                            $rdsService->startInstance($workspace, $schedule->resource_id);
                        } else {
                            $rdsService->stopInstance($workspace, $schedule->resource_id);
                        }
                    }

                    // Log activity
                    ActivityLog::create([
                        'workspace_id' => $workspace->id,
                        'actor_type' => 'schedule',
                        'action' => $schedule->action === 'start' ? 'started' : 'stopped',
                        'resource_type' => strtoupper($schedule->resource_type),
                        'resource_id' => $schedule->resource_id,
                        'resource_name' => $schedule->name,
                    ]);

                    // Update schedule
                    $schedule->update(['last_run_at' => $now]);
                } catch (\Exception $e) {
                    report($e);
                }
            });
    }
}
